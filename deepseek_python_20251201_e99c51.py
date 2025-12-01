# debug_json_structure.py
import requests
import json
import sys

def debug_json_structure(url):
    """调试JSON结构"""
    try:
        response = requests.get(url, timeout=30)
        data = response.json()
        
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
                    print(f"     内容预览: {str(data[key])[:100]}...")
        
        elif isinstance(data, list):
            print(f"数组长度: {len(data)}")
            if len(data) > 0:
                print("第一个元素类型: ", type(data[0]))
                if isinstance(data[0], dict):
                    print("第一个元素的键:", list(data[0].keys())[:10])
        
        print("\n完整JSON结构:")
        print(json.dumps(data, indent=2, ensure_ascii=False)[:2000] + "..." if len(json.dumps(data)) > 2000 else "")
        
    except Exception as e:
        print(f"错误: {e}")
        import traceback
        traceback.print_exc()

if __name__ == "__main__":
    url = "https://raw.githubusercontent.com/zzq1234567890/epg/main/traditional_channels_table.json"
    debug_json_structure(url)