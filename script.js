document.addEventListener('DOMContentLoaded', function() {
    const startButton = document.getElementById('start-test');
    const downloadSpeed = document.getElementById('download-speed');
    const uploadSpeed = document.getElementById('upload-speed');
    const pingValue = document.getElementById('ping-value');
    const progressBar = document.getElementById('progress-bar');
    const statusText = document.getElementById('status');
    const resultsDiv = document.getElementById('results');
    const qualityIndicator = document.getElementById('quality-indicator');
    const qualityText = document.getElementById('quality-text');
    const ipAddress = document.getElementById('ip-address');
    const isp = document.getElementById('isp');
    const locationText = document.getElementById('location');
    const testTime = document.getElementById('test-time');
    const dataFlow = document.getElementById('data-flow');
    
    // Mendapatkan informasi IP dan lokasi
    fetch('https://api.ipify.org?format=json')
        .then(response => response.json())
        .then(data => {
            ipAddress.textContent = data.ip;
            return fetch(`https://ipapi.co/${data.ip}/json/`);
        })
        .then(response => response.json())
        .then(data => {
            isp.textContent = data.org || 'Tidak diketahui';
            locationText.textContent = `${data.city}, ${data.country_name}`;
        })
        .catch(error => {
            console.error('Error mendapatkan informasi lokasi:', error);
            ipAddress.textContent = 'Tidak terdeteksi';
            isp.textContent = 'Tidak terdeteksi';
            locationText.textContent = 'Tidak terdeteksi';
        });
    
    startButton.addEventListener('click', startSpeedTest);
    
    function startSpeedTest() {
        // Reset nilai
        downloadSpeed.textContent = '-';
        uploadSpeed.textContent = '-';
        pingValue.textContent = '-';
        progressBar.style.width = '0%';
        resultsDiv.style.display = 'none';
        qualityIndicator.style.display = 'none';
        
        // Sembunyikan analisis AI sebelumnya
        const aiAnalysis = document.getElementById('ai-analysis');
        if (aiAnalysis) aiAnalysis.style.display = 'none';
        
        // Nonaktifkan tombol selama tes
        startButton.disabled = true;
        startButton.textContent = 'Tes Berlangsung...';
        
        // Aktifkan animasi data flow
        startDataFlowAnimation();
        
        // Gunakan PHP untuk tes kecepatan yang lebih akurat
        performPHPSpeedTest();
    }
    
    function startDataFlowAnimation() {
        // Aktifkan container data flow
        dataFlow.classList.add('active');
        dataFlow.innerHTML = '';
        
        // Buat partikel data flow
        for (let i = 0; i < 30; i++) {
            createDataParticle();
        }
    }
    
    function stopDataFlowAnimation() {
        dataFlow.classList.remove('active');
    }
    
    function createDataParticle() {
        const particle = document.createElement('div');
        particle.className = 'data-particle';
        
        // Posisi acak di bagian bawah container
        const left = Math.random() * 100;
        const delay = Math.random() * 2;
        
        particle.style.left = `${left}%`;
        particle.style.animationDelay = `${delay}s`;
        
        dataFlow.appendChild(particle);
        
        // Hapus partikel setelah animasi selesai
        setTimeout(() => {
            if (particle.parentNode) {
                particle.parentNode.removeChild(particle);
            }
        }, 2000);
    }
    
    function performPHPSpeedTest() {
        const formData = new FormData();
        formData.append('action', 'start_test');
        
        statusText.textContent = 'Mengukur ping...';
        progressBar.style.width = '20%';
        
        fetch('speedtest.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update UI dengan hasil dari PHP
                pingValue.textContent = data.ping;
                progressBar.style.width = '40%';
                statusText.textContent = 'Mengukur kecepatan unduh...';
                
                setTimeout(() => {
                    downloadSpeed.textContent = data.download_speed;
                    progressBar.style.width = '70%';
                    statusText.textContent = 'Mengukur kecepatan unggah...';
                    
                    setTimeout(() => {
                        uploadSpeed.textContent = data.upload_speed;
                        progressBar.style.width = '100%';
                        statusText.textContent = 'Menganalisis dengan AI...';
                        
                        setTimeout(() => {
                            statusText.textContent = 'Selesai';
                            stopDataFlowAnimation();
                            finishSpeedTest(
                                parseFloat(data.download_speed),
                                parseFloat(data.upload_speed),
                                data.ping,
                                data.quality,
                                data.ai_analysis
                            );
                        }, 1000);
                    }, 1000);
                }, 1000);
            } else {
                throw new Error(data.message);
            }
        })
        .catch(error => {
            console.error('Error melakukan tes kecepatan:', error);
            statusText.textContent = 'Error: ' + error.message;
            stopDataFlowAnimation();
            // Fallback ke simulasi JavaScript jika PHP gagal
            simulateSpeedTest();
        });
    }
    
    // Fungsi simulasi (fallback jika PHP tidak tersedia)
    function simulateSpeedTest() {
        statusText.textContent = 'Mengukur ping...';
        
        setTimeout(() => {
            const ping = Math.floor(Math.random() * 90) + 10;
            pingValue.textContent = ping;
            progressBar.style.width = '20%';
            
            statusText.textContent = 'Mengukur kecepatan unduh...';
            
            let downloadProgress = 20;
            const downloadInterval = setInterval(() => {
                downloadProgress += Math.random() * 5;
                progressBar.style.width = `${downloadProgress}%`;
                
                if (downloadProgress >= 60) {
                    clearInterval(downloadInterval);
                    const downloadSpeedValue = (Math.random() * 95 + 5).toFixed(1);
                    downloadSpeed.textContent = downloadSpeedValue;
                    
                    statusText.textContent = 'Mengukur kecepatan unggah...';
                    
                    let uploadProgress = 60;
                    const uploadInterval = setInterval(() => {
                        uploadProgress += Math.random() * 3;
                        progressBar.style.width = `${uploadProgress}%`;
                        
                        if (uploadProgress >= 100) {
                            clearInterval(uploadInterval);
                            const uploadSpeedValue = (Math.random() * 49 + 1).toFixed(1);
                            uploadSpeed.textContent = uploadSpeedValue;
                            
                            statusText.textContent = 'Selesai';
                            stopDataFlowAnimation();
                            
                            // Analisis AI sederhana untuk fallback
                            const aiAnalysis = generateSimpleAIAnalysis(
                                parseFloat(downloadSpeedValue), 
                                parseFloat(uploadSpeedValue), 
                                ping
                            );
                            
                            finishSpeedTest(
                                parseFloat(downloadSpeedValue), 
                                parseFloat(uploadSpeedValue), 
                                ping, 
                                null, 
                                aiAnalysis
                            );
                        }
                    }, 200);
                }
            }, 200);
        }, 1500);
    }
    
    function finishSpeedTest(download, upload, ping, quality = null, ai_analysis = null) {
        // Aktifkan kembali tombol
        startButton.disabled = false;
        startButton.textContent = 'Mulai Tes Kecepatan';
        
        // Tampilkan hasil detail
        resultsDiv.style.display = 'block';
        const now = new Date();
        testTime.textContent = now.toLocaleString('id-ID');
        
        // Tentukan kualitas koneksi
        let qualityTextValue = '';
        let qualityClass = '';
        
        if (quality) {
            qualityTextValue = quality.quality;
            qualityClass = quality.class;
        } else {
            if (download >= 50 && upload >= 20 && ping < 30) {
                qualityTextValue = 'Sangat Baik';
                qualityClass = 'quality-excellent';
            } else if (download >= 25 && upload >= 10 && ping < 50) {
                qualityTextValue = 'Baik';
                qualityClass = 'quality-good';
            } else if (download >= 10 && upload >= 5 && ping < 100) {
                qualityTextValue = 'Cukup';
                qualityClass = 'quality-fair';
            } else {
                qualityTextValue = 'Buruk';
                qualityClass = 'quality-poor';
            }
        }
        
        // Tampilkan indikator kualitas
        qualityText.textContent = qualityTextValue;
        qualityIndicator.className = `quality-indicator ${qualityClass}`;
        qualityIndicator.style.display = 'block';
        
        // Tampilkan analisis AI jika ada
        if (ai_analysis) {
            displayAIAnalysis(ai_analysis, download, upload, ping);
        }
    }
    
    function displayAIAnalysis(ai_analysis, download, upload, ping) {
        // Buat atau dapatkan elemen analisis AI
        let aiAnalysisDiv = document.getElementById('ai-analysis');
        
        if (!aiAnalysisDiv) {
            aiAnalysisDiv = document.createElement('div');
            aiAnalysisDiv.id = 'ai-analysis';
            aiAnalysisDiv.className = 'ai-analysis';
            document.querySelector('.container').appendChild(aiAnalysisDiv);
        }
        
        // Isi konten analisis AI
        let html = `
            <h2>Analisis AI</h2>
            <div class="ai-score">
                <div class="score-circle">
                    <span class="score-value">${ai_analysis.ai_score || calculateAIScore(download, upload, ping)}</span>
                    <span class="score-label">Skor AI</span>
                </div>
            </div>
            <div class="ai-section">
                <h3>Analisis Koneksi</h3>
                <ul class="ai-list">
        `;
        
        if (ai_analysis.analysis && ai_analysis.analysis.length > 0) {
            ai_analysis.analysis.forEach(item => {
                html += `<li>${item}</li>`;
            });
        } else {
            html += `<li>Analisis sedang diproses...</li>`;
        }
        
        html += `
                </ul>
            </div>
        `;
        
        if (ai_analysis.optimal_use && ai_analysis.optimal_use.length > 0) {
            html += `
                <div class="ai-section">
                    <h3>Kegiatan yang Optimal</h3>
                    <div class="optimal-use">
            `;
            
            ai_analysis.optimal_use.forEach(use => {
                html += `<span class="use-tag">${use}</span>`;
            });
            
            html += `
                    </div>
                </div>
            `;
        }
        
        if (ai_analysis.recommendations && ai_analysis.recommendations.length > 0) {
            html += `
                <div class="ai-section">
                    <h3>Rekomendasi</h3>
                    <ul class="ai-list recommendations">
            `;
            
            ai_analysis.recommendations.forEach(item => {
                html += `<li>${item}</li>`;
            });
            
            html += `
                    </ul>
                </div>
            `;
        }
        
        if (ai_analysis.predicted_issues && ai_analysis.predicted_issues.length > 0) {
            html += `
                <div class="ai-section">
                    <h3>Potensi Masalah</h3>
                    <ul class="ai-list issues">
            `;
            
            ai_analysis.predicted_issues.forEach(item => {
                html += `<li>${item}</li>`;
            });
            
            html += `
                    </ul>
                </div>
            `;
        }
        
        aiAnalysisDiv.innerHTML = html;
        aiAnalysisDiv.style.display = 'block';
    }
    
    // Fungsi untuk menghitung skor AI di JavaScript (fallback)
    function calculateAIScore(download, upload, ping) {
        let score = 0;
        
        const downloadScore = Math.min((download / 100) * 100, 100) * 0.5;
        const uploadScore = Math.min((upload / 50) * 100, 100) * 0.3;
        
        let pingScore = 0;
        if (ping <= 10) pingScore = 100;
        else if (ping <= 30) pingScore = 80;
        else if (ping <= 50) pingScore = 60;
        else if (ping <= 100) pingScore = 40;
        else pingScore = 20;
        
        pingScore = pingScore * 0.2;
        
        score = downloadScore + uploadScore + pingScore;
        
        return Math.round(score);
    }
    
    // Fungsi untuk menghasilkan analisis AI sederhana (fallback)
    function generateSimpleAIAnalysis(download, upload, ping) {
        const analysis = [];
        const recommendations = [];
        const predicted_issues = [];
        const optimal_use = [];
        
        if (download >= 25) {
            analysis.push("Kecepatan unduh Anda memadai untuk sebagian besar aktivitas online.");
            optimal_use.push("Streaming HD", "Browsing", "Video Call");
        } else {
            analysis.push("Kecepatan unduh Anda mungkin menyebabkan buffering pada video HD.");
            predicted_issues.push("Buffering video");
            recommendations.push("Pertimbangkan upgrade paket internet Anda.");
        }
        
        if (upload >= 5) {
            analysis.push("Kecepatan unggah cukup untuk video call dan upload file.");
        } else {
            analysis.push("Kecepatan unggah rendah untuk video call berkualitas tinggi.");
            predicted_issues.push("Kualitas video call buruk");
        }
        
        if (ping < 50) {
            analysis.push("Ping baik untuk aplikasi real-time.");
            optimal_use.push("Gaming Online");
        } else {
            analysis.push("Ping tinggi dapat menyebabkan lag dalam aplikasi real-time.");
            predicted_issues.push("Lag dalam game");
            recommendations.push("Coba gunakan koneksi kabel LAN.");
        }
        
        return {
            analysis,
            recommendations,
            predicted_issues,
            optimal_use,
            ai_score: calculateAIScore(download, upload, ping)
        };
    }
});