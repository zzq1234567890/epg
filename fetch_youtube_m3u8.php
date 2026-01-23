#!/usr/bin/env php
<?php
/**
 * YouTube直播M3U8获取脚本
 * 自动获取指定YouTube频道直播流的最大分辨率m3u8地址
 */

class YouTubeM3U8Generator {
    private $ytdlpPath = 'yt-dlp';
    private $channels = [];
    private $outputFile = 'yout.m3u';
    
    public function __construct() {
        // 定义要处理的YouTube频道
        $this->channels = [
            [
                'name' => '鳳凰衛視',
                'url' => 'https://www.youtube.com/watch?v=fN9uYWCjQaw',
                'id' => 'fenghuang'
            ],
            [
                'name' => 'CGTN',
                'url' => 'https://www.youtube.com/watch?v=_6dRRfnYJws',
                'id' => 'cgtn'
            ]
        ];
    }
    
    /**
     * 主执行函数
     */
    public function run() {
        echo "=== YouTube M3U8 生成器 ===\n";
        echo "开始时间: " . date('Y-m-d H:i:s') . "\n";
        
        // 检查yt-dlp是否可用
        if (!$this->checkYtDlp()) {
            echo "错误: yt-dlp 未安装或不可用\n";
            echo "请运行: pip install yt-dlp\n";
            exit(1);
        }
        
        // 创建M3U8文件
        $m3uContent = "#EXTM3U\n";
        $successCount = 0;
        
        foreach ($this->channels as $channel) {
            echo "\n正在处理: {$channel['name']}\n";
            echo "URL: {$channel['url']}\n";
            
            $result = $this->getChannelM3U8($channel);
            
            if ($result['success']) {
                // M3U格式: 第一行是频道名称，第二行是m3u8地址
                $m3uContent .= "# {$channel['name']}\n";
                $m3uContent .= $result['m3u8_url'] . "\n\n";
                
                $successCount++;
                
                echo "✓ 成功获取: {$result['resolution']}\n";
            } else {
                echo "✗ 失败: {$result['error']}\n";
            }
            
            // 添加延迟避免请求过快
            sleep(2);
        }
        
        // 写入文件
        $bytes = file_put_contents($this->outputFile, $m3uContent);
        
        echo "\n=== 完成 ===\n";
        echo "成功处理: {$successCount}/" . count($this->channels) . " 个频道\n";
        echo "输出文件: {$this->outputFile} (" . $bytes . " 字节)\n";
        echo "文件内容预览:\n";
        echo "--------------\n";
        echo $m3uContent;
        echo "--------------\n";
    }
    
    /**
     * 检查yt-dlp是否可用
     */
    private function checkYtDlp() {
        $command = "{$this->ytdlpPath} --version 2>&1";
        $output = shell_exec($command);
        return (strpos($output, 'yt-dlp') !== false);
    }
    
    /**
     * 获取频道的M3U8地址
     */
    private function getChannelM3U8($channel) {
        // 方法1: 尝试获取直播流的m3u8
        $m3u8 = $this->tryGetLiveM3U8($channel['url']);
        
        if ($m3u8['success']) {
            return $m3u8;
        }
        
        // 方法2: 如果直播流获取失败，尝试其他方法
        return $this->getAlternativeStream($channel['url']);
    }
    
    /**
     * 尝试获取直播M3U8
     */
    private function tryGetLiveM3U8($url) {
        // 使用yt-dlp获取最佳格式的直播流
        $command = sprintf(
            '%s --quiet --no-warnings -f "best" -g "%s" 2>&1',
            escapeshellarg($this->ytdlpPath),
            escapeshellarg($url)
        );
        
        $output = trim(shell_exec($command));
        
        if (empty($output)) {
            return [
                'success' => false,
                'error' => '获取直播流失败'
            ];
        }
        
        // 检查是否是m3u8
        $lines = explode("\n", $output);
        $streamUrl = $lines[0];
        
        if (strpos($streamUrl, '.m3u8') !== false) {
            // 尝试获取分辨率信息
            $resolution = $this->getStreamResolution($url);
            
            return [
                'success' => true,
                'm3u8_url' => $streamUrl,
                'resolution' => $resolution,
                'method' => 'direct_live_stream'
            ];
        }
        
        return [
            'success' => false,
            'error' => '不是m3u8格式',
            'raw_output' => $output
        ];
    }
    
    /**
     * 获取流的分辨率信息
     */
    private function getStreamResolution($url) {
        $command = sprintf(
            '%s --quiet --no-warnings --dump-json "%s" 2>&1 | head -c 5000',
            escapeshellarg($this->ytdlpPath),
            escapeshellarg($url)
        );
        
        $output = shell_exec($command);
        $data = json_decode($output, true);
        
        if ($data && isset($data['height'])) {
            return $data['height'] . 'p';
        }
        
        return '未知分辨率';
    }
    
    /**
     * 备用方法获取流
     */
    private function getAlternativeStream($url) {
        // 使用格式列表查找
        $command = sprintf(
            '%s --quiet --no-warnings --list-formats "%s" 2>&1',
            escapeshellarg($this->ytdlpPath),
            escapeshellarg($url)
        );
        
        $output = shell_exec($command);
        
        // 解析输出，查找最佳视频格式
        $lines = explode("\n", $output);
        $bestFormat = null;
        $maxHeight = 0;
        
        foreach ($lines as $line) {
            if (preg_match('/^(\d+)\s+\S+\s+(\d+)x(\d+)/', $line, $matches)) {
                $height = intval($matches[3]);
                if ($height > $maxHeight) {
                    $maxHeight = $height;
                    $bestFormat = $matches[1];
                }
            }
        }
        
        if ($bestFormat) {
            // 获取该格式的流地址
            $command = sprintf(
                '%s --quiet --no-warnings -f "%s" -g "%s" 2>&1',
                escapeshellarg($this->ytdlpPath),
                escapeshellarg($bestFormat),
                escapeshellarg($url)
            );
            
            $streamUrl = trim(shell_exec($command));
            
            if (!empty($streamUrl)) {
                return [
                    'success' => true,
                    'm3u8_url' => $streamUrl,
                    'resolution' => $maxHeight . 'p',
                    'method' => 'format_' . $bestFormat
                ];
            }
        }
        
        return [
            'success' => false,
            'error' => '无法获取任何流地址'
        ];
    }
}

// 执行
$generator = new YouTubeM3U8Generator();
$generator->run();
