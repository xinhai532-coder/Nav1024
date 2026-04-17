/**
 * 导航站业务逻辑模块
 * 包含：死链检测、数据导出导入、分享功能
 */

// --- 分享功能 ---
export function shareUrl(title, url, showToast) {
    if (navigator.share) {
        navigator.share({ title: title, url: url }).catch(() => {});
    } else {
        navigator.clipboard.writeText(`${title} - ${url}`).then(() => {
            showToast('链接已复制到剪贴板');
        }).catch(() => {
            prompt('复制链接：', `${title} - ${url}`);
        });
    }
}

// --- 导出数据 ---
export function exportData(navData, showToast) {
    const dataStr = JSON.stringify(navData, null, 2);
    const dataBlob = new Blob([dataStr], { type: 'application/json' });
    const url = URL.createObjectURL(dataBlob);
    
    const link = document.createElement('a');
    link.href = url;
    link.download = `导航数据备份_${new Date().toISOString().slice(0, 10)}.json`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
    
    showToast('✅ 数据已导出');
}

// --- 导入数据 ---
export function importData(event, callback) {
    const file = event.target.files[0];
    if (!file) return;
    
    const reader = new FileReader();
    reader.onload = function(e) {
        try {
            const importedData = JSON.parse(e.target.result);
            
            if (!Array.isArray(importedData)) {
                callback.showToast('❌ 文件格式错误');
                return;
            }
            
            const validatedData = importedData.map((item, index) => {
                return {
                    id: item.id || (index + 1),
                    category: item.category || '未分类',
                    title: item.title || '未知网站',
                    url: item.url || '#',
                    desc: item.desc || '',
                    icon: item.icon || (item.title ? item.title.charAt(0).toUpperCase() : '?')
                };
            });
            
            if (confirm(`确定要导入 ${validatedData.length} 条数据吗？\n这将覆盖当前所有数据！`)) {
                callback.onSuccess(validatedData);
            }
        } catch (error) {
            console.error('导入失败:', error);
            callback.showToast('❌ 导入失败，请检查文件');
        }
    };
    reader.readAsText(file);
    event.target.value = '';
}

// --- 死链检测 ---
export async function checkDeadLinks(navData, options) {
    const { showToast, onResult } = options;
    if (!confirm('开始检查所有链接是否可访问？这可能需要一些时间。')) return;
    
    const progressOverlay = document.createElement('div');
    progressOverlay.id = 'checkProgressOverlay';
    progressOverlay.style.cssText = `
        position: fixed; bottom: 75px; left: 50%; transform: translateX(-50%);
        background: rgba(0, 0, 0, 0.9); color: white; padding: 12px 20px;
        border-radius: 8px; z-index: 3000; min-width: 280px; text-align: center;
    `;
    progressOverlay.innerHTML = `
        <div style="margin-bottom: 6px; font-size: 14px;">🔍 正在检查...</div>
        <div style="background: #333; border-radius: 4px; overflow: hidden; height: 12px;">
            <div id="progressBar" style="background: linear-gradient(90deg, #4a90e2, #357abd); height: 100%; width: 0%; transition: width 0.3s;"></div>
        </div>
        <div id="progressText" style="margin-top: 6px; font-size: 12px; color: #aaa;">0 / ${navData.length}</div>
    `;
    document.body.appendChild(progressOverlay);
    
    try {
        const total = navData.length;
        let deadLinks = [];
        let checked = 0;
        
        const checkTasks = navData.map(item => {
            return (async () => {
                try {
                    const controller = new AbortController();
                    const timeoutId = setTimeout(() => controller.abort(), 30000);
                    const response = await fetch('api/check_links.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ urls: [{ id: item.id, url: item.url }] }),
                        signal: controller.signal
                    });
                    clearTimeout(timeoutId);
                    if (response.ok) {
                        const result = await response.json();
                        if (result.status === 'success' && result.results.length > 0) {
                            if (!result.results[0].accessible) {
                                deadLinks.push(item.title || item.url);
                            }
                        }
                    }
                } catch (e) {}
                checked++;
                const progress = Math.round((checked / total) * 100);
                const progressBar = document.getElementById('progressBar');
                const progressText = document.getElementById('progressText');
                if (progressBar) progressBar.style.width = progress + '%';
                if (progressText) progressText.textContent = `${checked} / ${total} (${progress}%)`;
            })();
        });
        
        const concurrency = 10;
        for (let i = 0; i < checkTasks.length; i += concurrency) {
            await Promise.all(checkTasks.slice(i, i + concurrency));
        }
        
        setTimeout(() => progressOverlay.remove(), 1000);
        
        if (typeof onResult === 'function') {
            onResult(deadLinks);
        } else if (deadLinks.length === 0) {
            showToast('✅ 检查完成！所有链接都正常');
        } else {
            showToast(`🔍 检查完成，发现 ${deadLinks.length} 个可能失效的链接`);
        }
    } catch (error) {
        progressOverlay.remove();
        showToast('❌ 检查失败，请重试');
    }
}
