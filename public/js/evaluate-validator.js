/**
 * Evaluate Document & Video Validator
 * ตรวจสอบความพร้อมของเอกสารและวิดีโอสำหรับระบบประเมิน
 */

const EvaluateValidator = {
    // ขนาดไฟล์สูงสุดสำหรับการฝัง (50 MB)
    MAX_EMBED_SIZE: 50 * 1024 * 1024,

    /**
     * ตรวจสอบว่า URL สามารถเข้าถึงได้หรือไม่
     */
    async checkUrlAccessibility(url) {
        if (!url || url.trim() === '') {
            return { accessible: false, error: 'URL ว่างเปล่า' };
        }

        try {
            // ตรวจสอบรูปแบบ URL
            const urlObj = new URL(url);
            
            // ตรวจสอบว่าเป็น YouTube, Google Drive, หรือ OneDrive หรือไม่
            const isYouTube = urlObj.hostname.includes('youtube.com') || urlObj.hostname.includes('youtu.be');
            const isGoogleDrive = urlObj.hostname.includes('drive.google.com');
            const isOneDrive = urlObj.hostname.includes('onedrive.live.com') || urlObj.hostname.includes('sharepoint.com');
            
            if (isYouTube) {
                return await this.checkYouTubeAccessibility(url);
            }
            
            if (isGoogleDrive) {
                return await this.checkGoogleDriveAccessibility(url);
            }

            // สำหรับ URL ทั่วไป ลอง fetch แบบ HEAD
            try {
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 5000);
                
                const response = await fetch(url, { 
                    method: 'HEAD', 
                    mode: 'no-cors',
                    signal: controller.signal
                });
                
                clearTimeout(timeoutId);
                
                return { 
                    accessible: true, 
                    type: 'general',
                    message: 'URL สามารถเข้าถึงได้' 
                };
            } catch (e) {
                // หาก CORS บล็อก ให้ถือว่า URL อาจเข้าถึงได้ (เป็น external link)
                return { 
                    accessible: true, 
                    type: 'external',
                    warning: 'ไม่สามารถตรวจสอบการเข้าถึงได้โดยตรง กรุณาตรวจสอบว่าเป็น public link'
                };
            }
        } catch (error) {
            return { 
                accessible: false, 
                error: 'URL ไม่ถูกต้อง: ' + error.message 
            };
        }
    },

    /**
     * ตรวจสอบ YouTube Video
     */
    async checkYouTubeAccessibility(url) {
        // แปลง URL เป็น video ID
        const videoId = this.extractYouTubeId(url);
        if (!videoId) {
            return { accessible: false, error: 'ไม่สามารถแยก YouTube Video ID ได้' };
        }

        // ตรวจสอบว่าเป็น embeddable หรือไม่
        try {
            const oEmbedUrl = `https://www.youtube.com/oembed?url=https://www.youtube.com/watch?v=${videoId}&format=json`;
            const response = await fetch(oEmbedUrl);
            
            if (response.ok) {
                const data = await response.json();
                return {
                    accessible: true,
                    type: 'youtube',
                    title: data.title,
                    author: data.author_name,
                    thumbnail: data.thumbnail_url,
                    message: 'วิดีโอสามารถเข้าถึงได้'
                };
            } else {
                return {
                    accessible: false,
                    type: 'youtube',
                    error: 'วิดีโออาจถูกตั้งค่าเป็น private หรือไม่พบ'
                };
            }
        } catch (e) {
            return {
                accessible: false,
                type: 'youtube',
                error: 'ไม่สามารถตรวจสอบวิดีโอได้'
            };
        }
    },

    /**
     * ตรวจสอบ Google Drive
     */
    async checkGoogleDriveAccessibility(url) {
        // แปลง URL เป็น file ID
        const fileId = this.extractGoogleDriveId(url);
        if (!fileId) {
            return { accessible: false, error: 'ไม่สามารถแยก Google Drive File ID ได้' };
        }

        // ตรวจสอบว่าเป็น public link หรือไม่
        const previewUrl = `https://drive.google.com/file/d/${fileId}/preview`;
        
        return {
            accessible: true,
            type: 'googledrive',
            previewUrl: previewUrl,
            downloadUrl: `https://drive.google.com/uc?export=download&id=${fileId}`,
            warning: 'กรุณาตรวจสอบว่าไฟล์ถูกตั้งค่าให้ "Anyone with the link can view"'
        };
    },

    /**
     * ตรวจสอบข้อมูลไฟล์ที่อัปโหลด
     */
    checkUploadedFile(file) {
        if (!file) {
            return { valid: false, error: 'ไม่พบไฟล์' };
        }

        const sizeMB = file.size / (1024 * 1024);
        const isLargeFile = file.size > this.MAX_EMBED_SIZE;
        
        const result = {
            valid: true,
            name: file.name,
            size: file.size,
            sizeMB: sizeMB.toFixed(2),
            type: file.type,
            extension: file.name.split('.').pop().toLowerCase(),
            isLargeFile: isLargeFile
        };

        // ตรวจสอบประเภทไฟล์ที่ยอมรับ
        const allowedTypes = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx'];
        if (!allowedTypes.includes(result.extension)) {
            result.valid = false;
            result.error = `ไฟล์ประเภท .${result.extension} ไม่รองรับ กรุณาใช้: ${allowedTypes.join(', ')}`;
            return result;
        }

        // ตรวจสอบขนาดไฟล์
        if (isLargeFile) {
            result.warning = `ไฟล์มีขนาด ${result.sizeMB} MB เกิน 50 MB ควรให้เป็นลิงก์แทนการฝัง`;
            result.recommendLink = true;
        }

        return result;
    },

    /**
     * สร้าง UI แสดงสถานะการตรวจสอบ
     */
    createStatusElement(status, type = 'url') {
        const div = document.createElement('div');
        div.className = `eval-status eval-status-${status.accessible ? 'success' : 'error'}`;
        
        let icon = status.accessible ? '✓' : '✗';
        let message = status.message || status.error || status.warning || '';
        
        div.innerHTML = `
            <span class="eval-status-icon">${icon}</span>
            <span class="eval-status-text">${message}</span>
        `;
        
        if (status.warning) {
            div.classList.add('eval-status-warning');
        }
        
        return div;
    },

    /**
     * Helper: แยก YouTube Video ID
     */
    extractYouTubeId(url) {
        const patterns = [
            /(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&\s?]+)/,
            /youtube\.com\/watch\?.*v=([^&\s]+)/
        ];
        
        for (const pattern of patterns) {
            const match = url.match(pattern);
            if (match) return match[1];
        }
        return null;
    },

    /**
     * Helper: แยก Google Drive File ID
     */
    extractGoogleDriveId(url) {
        const patterns = [
            /drive\.google\.com\/file\/d\/([^\/\s]+)/,
            /drive\.google\.com\/open\?id=([^&\s]+)/
        ];
        
        for (const pattern of patterns) {
            const match = url.match(pattern);
            if (match) return match[1];
        }
        return null;
    }
};

// Export สำหรับใช้งาน
if (typeof module !== 'undefined' && module.exports) {
    module.exports = EvaluateValidator;
}
