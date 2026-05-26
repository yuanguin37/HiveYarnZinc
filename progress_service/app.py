"""HiveYarnZinc 独立进度服务
监听 3001 端口，提供 RESTful API 存储/查询做题进度。
支持多用户隔离：每位用户的进度独立存储。
数据持久化到 /data/progress.json（Docker 卷挂载）。"""

from flask import Flask, request, jsonify
from flask_cors import CORS
import json, os, threading

app = Flask(__name__)
CORS(app)  # 允许来自 8080 前端页面的跨域请求

DATA_FILE = os.environ.get('PROGRESS_FILE', '/data/progress.json')
LOCK = threading.Lock()

CHALLENGE_TOTAL = 53  # 靶场总题目数


def load_all_progress():
    """从 JSON 文件加载全部用户的进度数据"""
    if os.path.exists(DATA_FILE):
        try:
            with open(DATA_FILE, 'r', encoding='utf-8') as f:
                data = json.load(f)
                if isinstance(data, dict):
                    return data
        except (json.JSONDecodeError, IOError):
            pass
    return {}


def save_all_progress(data):
    """持久化全部进度数据到 JSON 文件"""
    os.makedirs(os.path.dirname(DATA_FILE) or '.', exist_ok=True)
    with open(DATA_FILE, 'w', encoding='utf-8') as f:
        json.dump(data, f, indent=2, ensure_ascii=False)


def get_user_solved(all_data, username):
    """获取指定用户的解题记录"""
    if not username:
        return {}
    return all_data.get(username, {})


@app.route('/api/progress', methods=['GET'])
def get_progress():
    """GET /api/progress?user=xxx — 返回指定用户的进度
    如果没有 user 参数，返回空进度（兼容旧版）"""
    username = request.args.get('user', '')
    with LOCK:
        all_data = load_all_progress()
        user_solved = get_user_solved(all_data, username)

    return jsonify({
        'solved': user_solved,
        'solvedCount': len(user_solved),
        'total': CHALLENGE_TOTAL,
        'user': username
    })


@app.route('/api/progress', methods=['POST'])
def update_progress():
    """POST /api/progress — 更新某一题目的完成状态
    请求体: { "challengeId": "sqli", "completed": true, "user": "用户名" }
    """
    body = request.get_json(force=True, silent=True)
    if not body:
        return jsonify({'error': '请求体必须是 JSON'}), 400

    challenge_id = body.get('challengeId')
    if not challenge_id:
        return jsonify({'error': '缺少 challengeId'}), 400

    username = body.get('user', '')
    if not username:
        return jsonify({'error': '缺少 user（用户名）'}), 400

    completed = body.get('completed', True)

    with LOCK:
        all_data = load_all_progress()
        if username not in all_data:
            all_data[username] = {}

        if completed:
            all_data[username][challenge_id] = True
        else:
            all_data[username].pop(challenge_id, None)

        save_all_progress(all_data)

    user_solved = all_data[username]

    return jsonify({
        'solved': user_solved,
        'solvedCount': len(user_solved),
        'total': CHALLENGE_TOTAL,
        'user': username
    })


@app.route('/api/progress/reset', methods=['POST'])
def reset_progress():
    """POST /api/progress/reset?user=xxx — 清空指定用户的所有进度"""
    username = request.args.get('user', '')
    with LOCK:
        all_data = load_all_progress()
        if username:
            all_data[username] = {}
        save_all_progress(all_data)

    return jsonify({
        'solved': {},
        'solvedCount': 0,
        'total': CHALLENGE_TOTAL,
        'user': username
    })


@app.route('/api/rank', methods=['GET'])
def get_rank():
    """GET /api/rank — 返回所有用户的排行榜
    按解题数量降序排列，数量相同按用户名排序
    """
    with LOCK:
        all_data = load_all_progress()

    # 构建排行榜列表
    rank_list = []
    for username, solved in all_data.items():
        if not username:
            continue
        rank_list.append({
            'username': username,
            'solvedCount': len(solved),
            'total': CHALLENGE_TOTAL,
            'solvedIds': list(solved.keys())
        })

    # 按解题数量降序，相同则按用户名升序
    rank_list.sort(key=lambda x: (-x['solvedCount'], x['username']))

    # 添加排名
    for i, item in enumerate(rank_list, 1):
        item['rank'] = i

    return jsonify({
        'ranking': rank_list,
        'totalUsers': len(rank_list)
    })


@app.route('/health', methods=['GET'])
def health():
    """健康检查"""
    return jsonify({'status': 'ok'})


if __name__ == '__main__':
    print(f'[进度服务] 监听端口 3001，数据文件: {DATA_FILE}')
    print(f'[进度服务] 多用户隔离已启用')
    app.run(host='0.0.0.0', port=3001, debug=False)
