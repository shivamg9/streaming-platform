<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $stream->title }} - Live Stream</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #111;
            color: white;
            overflow: hidden;
        }

        .container {
            display: flex;
            height: 100vh;
            flex-direction: column;
        }

        .header {
            padding: 1rem;
            background: #1a1a1a;
            border-bottom: 1px solid #333;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stream-title {
            font-size: 1.2rem;
            font-weight: 600;
        }

        .stream-status {
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .status-live {
            background: #ef4444;
            color: white;
        }

        .status-ended {
            background: #6b7280;
            color: white;
        }

        .main-area {
            flex: 1;
            display: flex;
            position: relative;
        }

        .video-container {
            flex: 1;
            position: relative;
            background: #000;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .main-video {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .participants-grid {
            position: absolute;
            bottom: 20px;
            right: 20px;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            max-width: 300px;
        }

        .participant-video {
            width: 120px;
            height: 90px;
            background: #333;
            border-radius: 8px;
            border: 2px solid #555;
            object-fit: cover;
        }

        .participant-placeholder {
            width: 120px;
            height: 90px;
            background: #333;
            border-radius: 8px;
            border: 2px solid #555;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            font-size: 0.75rem;
        }

        .controls {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 1rem;
            background: rgba(0, 0, 0, 0.8);
            padding: 1rem;
            border-radius: 12px;
            backdrop-filter: blur(10px);
        }

        .control-btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            background: #3b82f6;
            color: white;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
        }

        .control-btn:hover {
            background: #2563eb;
        }

        .control-btn.danger {
            background: #ef4444;
        }

        .control-btn.danger:hover {
            background: #dc2626;
        }

        .control-btn:disabled {
            background: #6b7280;
            cursor: not-allowed;
        }

        .camera-selector {
            background: rgba(0, 0, 0, 0.8);
            padding: 1rem;
            border-radius: 12px;
            margin-right: 1rem;
        }

        .camera-option {
            padding: 0.5rem;
            margin: 0.25rem 0;
            background: #333;
            border: none;
            border-radius: 6px;
            color: white;
            cursor: pointer;
            width: 100%;
            text-align: left;
        }

        .camera-option:hover {
            background: #555;
        }

        .camera-option.active {
            background: #3b82f6;
        }

        .sidebar {
            width: 300px;
            background: #1a1a1a;
            border-left: 1px solid #333;
            padding: 1rem;
            overflow-y: auto;
        }

        .sidebar-section {
            margin-bottom: 2rem;
        }

        .sidebar-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #3b82f6;
        }

        .video-item {
            display: flex;
            align-items: center;
            padding: 0.75rem;
            background: #2a2a2a;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            cursor: pointer;
        }

        .video-item:hover {
            background: #333;
        }

        .video-thumbnail {
            width: 60px;
            height: 45px;
            background: #444;
            border-radius: 4px;
            margin-right: 0.75rem;
            object-fit: cover;
        }

        .video-info h4 {
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        .video-info p {
            font-size: 0.75rem;
            color: #999;
        }

        .play-btn {
            padding: 0.5rem 1rem;
            background: #3b82f6;
            border: none;
            border-radius: 6px;
            color: white;
            font-size: 0.875rem;
            cursor: pointer;
            margin-top: 0.5rem;
        }

        .play-btn:hover {
            background: #2563eb;
        }

        .participant-item {
            display: flex;
            align-items: center;
            padding: 0.75rem;
            background: #2a2a2a;
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }

        .participant-avatar {
            width: 40px;
            height: 40px;
            background: #3b82f6;
            border-radius: 50%;
            margin-right: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .participant-info h4 {
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        .participant-role {
            font-size: 0.75rem;
            color: #999;
        }

        .hidden {
            display: none !important;
        }

        .loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 1.1rem;
            color: #999;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: absolute;
                right: 0;
                top: 0;
                height: 100%;
                z-index: 1000;
                transform: translateX(100%);
                transition: transform 0.3s;
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .participants-grid {
                grid-template-columns: repeat(1, 1fr);
                max-width: 150px;
            }

            .participant-video,
            .participant-placeholder {
                width: 100px;
                height: 75px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="stream-title">{{ $stream->title }}</div>
            <div class="stream-status status-live">● LIVE</div>
        </div>

        <div class="main-area">
            <div class="video-container">
                <video id="mainVideo" class="main-video" autoplay muted></video>
                <div id="loading" class="loading">Loading stream...</div>

                <div class="participants-grid" id="participantsGrid"></div>

                <div class="controls">
                    <select id="cameraSelect" class="camera-selector">
                        <option value="">Select Camera</option>
                    </select>
                    <button id="startStreamBtn" class="control-btn">Start Stream</button>
                    <button id="stopStreamBtn" class="control-btn danger">Stop Stream</button>
                    <button id="toggleSidebar" class="control-btn">Videos</button>
                </div>
            </div>

            <div class="sidebar" id="sidebar">
                <div class="sidebar-section">
                    <div class="sidebar-title">Uploaded Videos</div>
                    <div id="videosList">
                        <p style="color: #999; text-align: center; padding: 2rem;">Loading videos...</p>
                    </div>
                </div>

                <div class="sidebar-section">
                    <div class="sidebar-title">Participants</div>
                    <div id="participantsList">
                        <p style="color: #999; text-align: center; padding: 2rem;">Loading participants...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        class StreamManager {
            constructor() {
                this.stream = @json($stream);
                this.currentUser = @json(auth()->user());
                this.localStream = null;
                this.peerConnections = {};
                this.isStreaming = false;
                this.selectedCamera = '';
                this.socket = null;

                this.initializeElements();
                this.setupEventListeners();
                this.loadParticipants();
                this.loadVideos();
                this.initializeWebRTC();
            }

            initializeElements() {
                this.mainVideo = document.getElementById('mainVideo');
                this.loading = document.getElementById('loading');
                this.cameraSelect = document.getElementById('cameraSelect');
                this.startStreamBtn = document.getElementById('startStreamBtn');
                this.stopStreamBtn = document.getElementById('stopStreamBtn');
                this.sidebar = document.getElementById('sidebar');
                this.videosList = document.getElementById('videosList');
                this.participantsList = document.getElementById('participantsList');
                this.participantsGrid = document.getElementById('participantsGrid');
            }

            setupEventListeners() {
                this.cameraSelect.addEventListener('change', () => this.switchCamera());
                this.startStreamBtn.addEventListener('click', () => this.startStream());
                this.stopStreamBtn.addEventListener('click', () => this.stopStream());

                // Listen for stream events
                window.Echo.private(`stream.${this.stream.id}`)
                    .listen('.stream.started', (e) => this.onStreamStarted(e))
                    .listen('.stream.ended', (e) => this.onStreamEnded(e))
                    .listen('.participant.joined', (e) => this.onParticipantJoined(e))
                    .listen('.participant.left', (e) => this.onParticipantLeft(e));
            }

            async initializeWebRTC() {
                try {
                    // Get available cameras
                    const devices = await navigator.mediaDevices.enumerateDevices();
                    const cameras = devices.filter(device => device.kind === 'videoinput');

                    cameras.forEach((camera, index) => {
                        const option = document.createElement('option');
                        option.value = camera.deviceId;
                        option.textContent = camera.label || `Camera ${index + 1}`;
                        this.cameraSelect.appendChild(option);
                    });

                    if (cameras.length > 0) {
                        this.selectedCamera = cameras[0].deviceId;
                    }
                } catch (error) {
                    console.error('Error initializing WebRTC:', error);
                }
            }

            async switchCamera() {
                this.selectedCamera = this.cameraSelect.value;
                if (this.localStream) {
                    this.localStream.getTracks().forEach(track => track.stop());
                }
                await this.startLocalStream();
            }

            async startLocalStream() {
                try {
                    const constraints = {
                        video: {
                            deviceId: this.selectedCamera ? { exact: this.selectedCamera } : undefined,
                            width: { ideal: 1280 },
                            height: { ideal: 720 }
                        },
                        audio: true
                    };

                    this.localStream = await navigator.mediaDevices.getUserMedia(constraints);
                    this.mainVideo.srcObject = this.localStream;
                    this.mainVideo.muted = true;
                } catch (error) {
                    console.error('Error starting local stream:', error);
                    alert('Error accessing camera/microphone. Please check permissions.');
                }
            }

            async startStream() {
                if (this.isStreaming) return;

                try {
                    await this.startLocalStream();

                    // Start the stream via API
                    const response = await fetch(`/api/streams/${this.stream.id}/start`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.isStreaming = true;
                        this.startStreamBtn.disabled = true;
                        this.stopStreamBtn.disabled = false;
                        this.loading.style.display = 'none';

                        // Initialize WebRTC streaming
                        await this.initializeStreaming();
                    } else {
                        alert(result.message || 'Failed to start stream');
                    }
                } catch (error) {
                    console.error('Error starting stream:', error);
                    alert('Failed to start stream');
                }
            }

            async stopStream() {
                if (!this.isStreaming) return;

                try {
                    // Stop the stream via API
                    const response = await fetch(`/api/streams/${this.stream.id}/stop`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.isStreaming = false;
                        this.startStreamBtn.disabled = false;
                        this.stopStreamBtn.disabled = true;

                        // Stop local stream
                        if (this.localStream) {
                            this.localStream.getTracks().forEach(track => track.stop());
                            this.localStream = null;
                        }

                        this.mainVideo.srcObject = null;
                        this.loading.style.display = 'block';
                    } else {
                        alert(result.message || 'Failed to stop stream');
                    }
                } catch (error) {
                    console.error('Error stopping stream:', error);
                    alert('Failed to stop stream');
                }
            }

            async initializeStreaming() {
                // This would integrate with a streaming service like Agora, WebRTC, or similar
                // For demo purposes, we'll just show the local stream
                console.log('Streaming initialized');
            }

            async loadVideos() {
                try {
                    const response = await fetch('/api/videos', {
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.displayVideos(result.videos.data);
                    }
                } catch (error) {
                    console.error('Error loading videos:', error);
                }
            }

            displayVideos(videos) {
                if (videos.length === 0) {
                    this.videosList.innerHTML = '<p style="color: #999; text-align: center; padding: 2rem;">No videos uploaded yet</p>';
                    return;
                }

                this.videosList.innerHTML = '';

                videos.forEach(video => {
                    const videoElement = document.createElement('div');
                    videoElement.className = 'video-item';
                    videoElement.innerHTML = `
                        <img src="${video.thumbnail_url || '/placeholder-video.png'}" alt="${video.title}" class="video-thumbnail" onerror="this.style.display='none'">
                        <div class="video-info">
                            <h4>${video.title}</h4>
                            <p>${video.description || 'No description'}</p>
                            <button class="play-btn" onclick="streamManager.playVideo(${video.id})">Play in Stream</button>
                        </div>
                    `;
                    this.videosList.appendChild(videoElement);
                });
            }

            async playVideo(videoId) {
                try {
                    // This would trigger playing the video in the stream
                    console.log('Playing video:', videoId);
                    // Implementation would depend on your streaming setup
                } catch (error) {
                    console.error('Error playing video:', error);
                }
            }

            async loadParticipants() {
                try {
                    const response = await fetch(`/api/streams/${this.stream.id}/participants`, {
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.displayParticipants(result.participants);
                    }
                } catch (error) {
                    console.error('Error loading participants:', error);
                }
            }

            displayParticipants(participants) {
                this.participantsList.innerHTML = '';

                participants.forEach(participant => {
                    const participantElement = document.createElement('div');
                    participantElement.className = 'participant-item';
                    participantElement.innerHTML = `
                        <div class="participant-avatar">${participant.user.name.charAt(0).toUpperCase()}</div>
                        <div class="participant-info">
                            <h4>${participant.user.name}</h4>
                            <div class="participant-role">${participant.role}</div>
                        </div>
                    `;
                    this.participantsList.appendChild(participantElement);
                });
            }

            onStreamStarted(event) {
                console.log('Stream started:', event);
                this.loading.style.display = 'none';
            }

            onStreamEnded(event) {
                console.log('Stream ended:', event);
                this.loading.style.display = 'block';
                this.isStreaming = false;
                this.startStreamBtn.disabled = false;
                this.stopStreamBtn.disabled = true;
            }

            onParticipantJoined(event) {
                console.log('Participant joined:', event);
                this.loadParticipants(); // Refresh participants list
            }

            onParticipantLeft(event) {
                console.log('Participant left:', event);
                this.loadParticipants(); // Refresh participants list
            }
        }

        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', function() {
            window.streamManager = new StreamManager();
        });
    </script>
</body>
</html>
