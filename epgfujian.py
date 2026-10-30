import hashlib
import time
import re
import xml.etree.ElementTree as ET
from xml.dom import minidom
from datetime import datetime, timedelta, timezone      # 新增 timezone
from curl_cffi import requests as cffi_requests
from bs4 import BeautifulSoup


class FJTV_EPG:
    """福建電視台 EPG """

    # 已确认的频道列表
    CHANNELS = {
        "zhpd": {"id": "665248990102917120", "name": "福建綜合",      "xml_id": "fjtv-zhpd"},
        "xwpd": {"id": "665248914378952704", "name": "福建新聞",      "xml_id": "fjtv-xwpd"},
        "dspd": {"id": "665248752898248704", "name": "福建文旅體育", "xml_id": "fjtv-wlty"},
        "sepd": {"id": "665248553475870720", "name": "福建少兒頻道",      "xml_id": "fjtv-sepd"},
         "setv":{"id": "665248966136664064", "name": "東南衛視",      "xml_id": "fjtv-setv"},
        "setv":{"id": "665248523855695872", "name": "海峽衛視",      "xml_id": "fjtv-hxtv"},
    }

    API_KEY    = "877a9ba7a98f75b90a9d49f53f15a858"
    BASE64_KEY = "NjhhMDRiODE3N2JkYzllNWUxNmE4OWU2Nzc3YTdiNjY="
    VERSION    = "1.0.0"
    BASE_URL   = "https://live.fjtv.net"

    def __init__(self):
        self.session = cffi_requests.Session(impersonate="chrome")
        # 先访问页面建立会话（绕过 WAF）
        self.session.get(f"{self.BASE_URL}/dspd/", timeout=15)

    def _sign(self):
        """生成 API 签名请求头（逆向自 m2obase.js）"""
        ts = int(time.time())
        raw = f"{self.API_KEY}&{self.BASE64_KEY}&{self.VERSION}&{ts}"
        return {
            "X-API-TIMESTAMP": str(ts),
            "X-API-KEY":       self.API_KEY,
            "X-AUTH-TYPE":     "md5",
            "X-API-VERSION":   self.VERSION,
            "X-API-SIGNATURE": hashlib.md5(raw.encode()).hexdigest(),
        }

    def fetch(self, channel_code, date_str):
        """获取指定频道、日期的节目单"""
        ch = self.CHANNELS[channel_code]
        r = self.session.get(
            f"{self.BASE_URL}/m2o/program_switch.php",
            params={"channel_id": ch["id"], "dates": date_str, "shownums": "7"},
            headers={
                "Referer": f"{self.BASE_URL}/{channel_code}/",
                "X-Requested-With": "XMLHttpRequest",
                **self._sign(),
            },
            timeout=15,
        )
        r.raise_for_status()

        soup = BeautifulSoup(r.text, "html.parser")
        name_el = soup.find(id="liveChannelName")
        # 使用预定义的频道名称，而不是API返回的名称（保持一致性）
        ch_name = ch["name"]

        programs = []
        sched = soup.find(id="liveSchedule")
        if sched:
            for li in sched.find_all("li"):
                t_el = li.find("span", class_="time")
                if t_el:
                    t = t_el.get_text(strip=True)
                    title = li.get_text(strip=True).replace(t, "", 1)
                    title = re.sub(r"^当前直播", "", title).strip()
                    programs.append((t, title))

        return {
            "channel": ch_name,          # 使用预定义的带"福建"前缀的名称
            "xml_id": ch["xml_id"],
            "date": date_str,
            "programs": programs,
        }

    def build_xmltv(self, all_epg):
        """生成 XMLTV 格式 XML，channel 使用中文名称"""
        tv = ET.Element("tv", {
            "generator-info-name": "秋哥",
            "source-info-name": "福建電視台",
        })

        seen = set()
        for epg in all_epg:
            # 使用中文频道名称作为 channel id（如"福建綜合頻道"）
            channel_id = epg["channel"]
            
            # 频道节点（去重）
            if channel_id not in seen:
                seen.add(channel_id)
                ch = ET.SubElement(tv, "channel", {"id": channel_id})
                ET.SubElement(ch, "display-name", {"lang": "zh"}).text = epg["channel"]

            # 节目节点
            base = datetime.strptime(epg["date"], "%Y-%m-%d")
            fmt = "%Y%m%d%H%M%S +0800"
            progs = epg["programs"]

            for i, (t, title) in enumerate(progs):
                h, m, s = map(int, t.split(":"))
                start = base.replace(hour=h, minute=m, second=s)
                if i + 1 < len(progs):
                    nh, nm, ns = map(int, progs[i + 1][0].split(":"))
                    stop = base.replace(hour=nh, minute=nm, second=ns)
                else:
                    stop = start + timedelta(minutes=5)

                # programme 的 channel 属性也使用中文名称
                p = ET.SubElement(tv, "programme", {
                    "start": start.strftime(fmt),
                    "stop":  stop.strftime(fmt),
                    "channel": channel_id,
                })
                ET.SubElement(p, "title", {"lang": "zh"}).text = title
                ET.SubElement(p, "desc",  {"lang": "zh"}).text = f"{epg['channel']} - {title}"
                ET.SubElement(p, "category", {"lang": "zh"}).text = "綜合"

        raw = ET.tostring(tv, encoding="unicode")
        return minidom.parseString(raw).toprettyxml(indent="  ", encoding="utf-8")


if __name__ == "__main__":
    scraper = FJTV_EPG()
    # 使用中国时区（UTC+8）获取当天日期
    china_tz = timezone(timedelta(hours=8))
    today = datetime.now(china_tz).strftime("%Y-%m-%d")
    all_epg = []

    for code in scraper.CHANNELS:
        try:
            epg = scraper.fetch(code, today)
            all_epg.append(epg)
            print(f"✅ {epg['channel']:<14} | {len(epg['programs']):>2} 个节目")
        except Exception as e:
            print(f"❌ 获取 {code} 失败: {e}")

    if all_epg:
        xml_bytes = scraper.build_xmltv(all_epg)
        with open("epgfujian.xml", "wb") as f:
            f.write(xml_bytes)
        print(f"\n📄 已保存 epgfujian.xml ({len(xml_bytes):,} bytes)")
    else:
        print("\n⚠️ 没有获取到任何节目数据")
