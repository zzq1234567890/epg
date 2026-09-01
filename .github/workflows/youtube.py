name: Update M3U with m3u8 Streams

on:
  schedule:
    - cron: "0 */6 * * *"
  workflow_dispatch:

jobs:
  build:
    runs-on: ubuntu-latest
    env:
      ACTIONS_ALLOW_USE_UNSECURE_NODE_VERSION: true   # 忽略 Node 警告

    steps:
      - uses: actions/checkout@v4
        with:
          token: ${{ secrets.GITHUB_TOKEN }}

      - name: 设置 Python 3.11
        uses: actions/setup-python@v5
        with:
          python-version: "3.11"

      - name: 安装依赖
        run: |
          pip install --upgrade pip
          pip install yt-dlp

      - name: 准备 Cookies（如果有 Secret）
        env:
          YT_COOKIES: ${{ secrets.YOUTUBE_COOKIES }}
        run: |
          if [ -n "$YT_COOKIES" ]; then
            echo "$YT_COOKIES" > cookies.txt
          fi

      - name: 运行脚本
        run: python generate_m3u.py
        env:
          YTDL_COOKIES_FILE: cookies.txt   # 脚本会检查此文件

      - name: 复制结果到目标目录
        run: |
          mkdir -p streams
          cp output.m3u streams/iptv.m3u

      - name: 提交更改
        run: |
          git config user.name "github-actions[bot]"
          git config user.email "github-actions[bot]@users.noreply.github.com"
          git add streams/iptv.m3u || true
          git diff --staged --quiet || git commit -m "chore: update m3u [skip ci]"
          git push
