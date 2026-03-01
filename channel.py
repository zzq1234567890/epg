# debug_json_structure.py
import requests
import json
import sys
from urllib.parse import urlparse

def validate_url(url):
    """验证URL是否安全，仅允许HTTP/HTTPS协议和特定的可信域名"""
    try:
        parsed = urlparse(url)
        # 只允许http和https协议
        if parsed.scheme not in ('http', 'https'):
            raise ValueError("只允许HTTP或HTTPS协议")
        # 限制域名（可根据实际需要修改）
        allowed_domains = ('raw.githubusercontent.com', 'github.com')
        if not parsed.netloc.endswith(allowed_domains):
            raise ValueError(f"域名不在允许列表中: {parsed.netloc}")
        return True
    except Exception as e:
        print(f"URL验证失败: {e}")
        return False

def debug_json_structure(url, max_size=10*1024*1024):
    """调试JSON结构，限制最大响应大小为10MB"""
    if not validate_url(url):
        print("无效的URL，终止执行")
        return

    try:
        # 使用stream=True流式读取，限制大小
        response = requests.get(url, timeout=30, stream=True)
        response.raise_for_status()  # 检查HTTP状态码

        # 读取响应内容，限制大小
        content = b''
        for chunk in response.iter_content(chunk_size=8192):
            content += chunk
            if len(content) > max_size:
                raise ValueError(f"响应内容超过最大允许大小 ({max_size/1024/1024:.1f} MB)")

        # 解析JSON
        data = json.loads(content.decode('utf-8'))

        print("="*50)
        print("JSON 数据结构分析")
        print("="*50)

        print(f"数据类型: {type(data)}")

        if isinstance(data, dict):
            print(f"字典键数量: {len(data)}")
            print("前10个键:")
            for i, key in enumerate(list(data.keys())[:10]):
                print(f"  {i+1}. {key}: {type(data[key])}")
                if isinstance(data[key], (dict, list)):
                    preview = str(data[key])[:100]
                    print(f"     内容预览: {preview}...")

        elif isinstance(data, list):
            print(f"数组长度: {len(data)}")
            if len(data) > 0:
                print("第一个元素类型: ", type(data[0]))
                if isinstance(data[0], dict):
                    print("第一个元素的键:", list(data[0].keys())[:10])

        print("\n完整JSON结构:")
        json_str = json.dumps(data, indent=2, ensure_ascii=False)
        if len(json_str) > 2000:
            print(json_str[:2000] + "...")
        else:
            print(json_str)

    except requests.exceptions.RequestException as e:
        print(f"网络请求错误: {e}")
    except json.JSONDecodeError as e:
        print(f"JSON解析错误: {e}")
    except Exception as e:
        print(f"未知错误: {e}")
        import traceback
        traceback.print_exc()

def main():
    url = "https://raw.githubusercontent.com/zzq1234567890/epg/main/traditional_channels_table.json"
    debug_json_structure(url)

if __name__ == "__main__":
    main()
