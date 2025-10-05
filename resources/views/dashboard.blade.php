<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Streaming Platform - Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8fafc;
            color: #334155;
        }

        .header {
            background: white;
            padding: 1rem 2rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: #3b82f6;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-name {
            font-weight: 500;
        }

        .logout-btn {
            padding: 0.5rem 1rem;
            background: #ef4444;
            color: white;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.875rem;
            transition: background 0.2s;
        }

        .logout-btn:hover {
            background: #dc2626;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 2rem;
            color: #1e293b;
        }

        .create-stream-section {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 3rem;
            border: 1px solid #e2e8f0;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: #1e293b;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-label {
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #475569;
        }

        .form-input, .form-select, .form-textarea {
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.875rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-textarea {
            min-height: 100px;
            resize: vertical;
        }

        .create-btn {
            padding: 0.75rem 2rem;
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
        }

        .create-btn:hover {
            background: #2563eb;
        }

        .create-btn:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }

        .streams-section {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
        }

        .streams-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .stream-card {
            padding: 1.5rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stream-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .stream-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #1e293b;
        }

        .stream-description {
            font-size: 0.875rem;
            color: #64748b;
            margin-bottom: 1rem;
            line-height: 1.5;
        }

        .stream-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            font-size: 0.75rem;
            color: #64748b;
        }

        .stream-status {
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-active {
            background: #dcfce7;
            color: #166534;
        }

        .status-scheduled {
            background: #fef3c7;
            color: #92400e;
        }

        .status-ended {
            background: #f1f5f9;
            color: #475569;
        }

        .stream-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: #3b82f6;
            color: white;
        }

        .btn-primary:hover {
            background: #2563eb;
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-success:hover {
            background: #059669;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .loading {
            text-align: center;
            padding: 3rem;
            color: #64748b;
        }

        .error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
        }

        .success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .header {
                padding: 1rem;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .streams-grid {
                grid-template-columns: 1fr;
            }

            .stream-actions {
                flex-direction: column;
            }

            .btn {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="logo">StreamYard Clone</div>
        <div class="user-info">
            <span class="user-name">Welcome, {{ Auth::user()->name }}!</span>
            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <button type="submit" class="logout-btn">Logout</button>
            </form>
        </div>
    </header>

    <div class="container">
        <h1 class="page-title">Live Streaming Dashboard</h1>

        <div class="create-stream-section">
            <h2 class="section-title">Create New Stream</h2>

            <div id="message" style="display: none;"></div>

            <form id="createStreamForm">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Stream Title *</label>
                        <input type="text" id="title" name="title" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Stream Type</label>
                        <select id="type" name="type" class="form-select">
                            <option value="live">Live Stream</option>
                            <option value="prerecorded">Pre-recorded</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Max Participants</label>
                        <input type="number" id="max_participants" name="max_participants" class="form-input" value="10" min="1" max="50">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Scheduled Start Time</label>
                        <input type="datetime-local" id="scheduled_start_time" name="scheduled_start_time" class="form-input">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea id="description" name="description" class="form-textarea" placeholder="Optional description for your stream..."></textarea>
                </div>

                <button type="submit" class="create-btn" id="createBtn">Create Stream</button>
            </form>
        </div>

        <div class="streams-section">
            <h2 class="section-title">Your Streams</h2>
            <div id="streamsList" class="loading">
                Loading your streams...
            </div>
        </div>
    </div>

    <script>
        class Dashboard {
            constructor() {
                this.initializeElements();
                this.setupEventListeners();
                this.loadStreams();
            }

            initializeElements() {
                this.createStreamForm = document.getElementById('createStreamForm');
                this.createBtn = document.getElementById('createBtn');
                this.streamsList = document.getElementById('streamsList');
                this.message = document.getElementById('message');
            }

            setupEventListeners() {
                this.createStreamForm.addEventListener('submit', (e) => this.createStream(e));
            }

            async createStream(e) {
                e.preventDefault();

                const formData = new FormData(e.target);
                const data = {
                    title: formData.get('title'),
                    description: formData.get('description'),
                    type: formData.get('type'),
                    max_participants: formData.get('max_participants'),
                    scheduled_start_time: formData.get('scheduled_start_time') || null,
                    is_public: true
                };

                this.createBtn.disabled = true;
                this.createBtn.textContent = 'Creating...';

                try {
                    const response = await fetch('/api/streams', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(data)
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.showMessage('Stream created successfully!', 'success');
                        e.target.reset();
                        this.loadStreams();

                        // Redirect to stream after 2 seconds
                        setTimeout(() => {
                            window.location.href = `/streams/${result.stream.id}`;
                        }, 2000);
                    } else {
                        this.showMessage(result.message || 'Failed to create stream', 'error');
                    }
                } catch (error) {
                    console.error('Error creating stream:', error);
                    this.showMessage('Failed to create stream. Please try again.', 'error');
                } finally {
                    this.createBtn.disabled = false;
                    this.createBtn.textContent = 'Create Stream';
                }
            }

            async loadStreams() {
                try {
                    const response = await fetch('/api/streams', {
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.displayStreams(result.streams || []);
                    } else {
                        this.streamsList.innerHTML = '<p style="color: #64748b;">Failed to load streams</p>';
                    }
                } catch (error) {
                    console.error('Error loading streams:', error);
                    this.streamsList.innerHTML = '<p style="color: #64748b;">Failed to load streams</p>';
                }
            }

            displayStreams(streams) {
                if (streams.length === 0) {
                    this.streamsList.innerHTML = `
                        <div style="text-align: center; padding: 3rem; color: #64748b;">
                            <p style="font-size: 1.1rem; margin-bottom: 1rem;">No streams yet</p>
                            <p>Create your first stream to get started!</p>
                        </div>
                    `;
                    return;
                }

                const streamsHtml = streams.map(stream => `
                    <div class="stream-card">
                        <h3 class="stream-title">${this.escapeHtml(stream.title)}</h3>
                        <p class="stream-description">${this.escapeHtml(stream.description || 'No description')}</p>

                        <div class="stream-meta">
                            <span>Type: ${stream.type}</span>
                            <span class="stream-status status-${stream.status}">
                                ${stream.status.charAt(0).toUpperCase() + stream.status.slice(1)}
                            </span>
                        </div>

                        <div class="stream-actions">
                            <a href="/streams/${stream.id}" class="btn btn-primary">View Stream</a>
                            ${stream.status === 'active' ?
                                `<button onclick="dashboard.startStream(${stream.id})" class="btn btn-success">Start</button>` :
                                `<button onclick="dashboard.stopStream(${stream.id})" class="btn btn-danger">Stop</button>`
                            }
                            <button onclick="dashboard.copyStreamLink(${stream.id})" class="btn btn-secondary">Copy Link</button>
                        </div>
                    </div>
                `).join('');

                this.streamsList.innerHTML = streamsHtml;
            }

            async startStream(streamId) {
                try {
                    const response = await fetch(`/api/streams/${streamId}/start`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.showMessage('Stream started successfully!', 'success');
                        this.loadStreams();
                    } else {
                        this.showMessage(result.message || 'Failed to start stream', 'error');
                    }
                } catch (error) {
                    console.error('Error starting stream:', error);
                    this.showMessage('Failed to start stream', 'error');
                }
            }

            async stopStream(streamId) {
                try {
                    const response = await fetch(`/api/streams/${streamId}/stop`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.showMessage('Stream stopped successfully!', 'success');
                        this.loadStreams();
                    } else {
                        this.showMessage(result.message || 'Failed to stop stream', 'error');
                    }
                } catch (error) {
                    console.error('Error stopping stream:', error);
                    this.showMessage('Failed to stop stream', 'error');
                }
            }

            copyStreamLink(streamId) {
                const url = `${window.location.origin}/streams/${streamId}`;
                navigator.clipboard.writeText(url).then(() => {
                    this.showMessage('Stream link copied to clipboard!', 'success');
                }).catch(() => {
                    this.showMessage('Failed to copy link', 'error');
                });
            }

            showMessage(message, type) {
                this.message.textContent = message;
                this.message.className = type;
                this.message.style.display = 'block';

                setTimeout(() => {
                    this.message.style.display = 'none';
                }, 5000);
            }

            escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
        }

        // Initialize dashboard when page loads
        document.addEventListener('DOMContentLoaded', function() {
            window.dashboard = new Dashboard();
        });
    </script>
</body>
</html>
