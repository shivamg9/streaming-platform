# Next Era Media Group - Live Streaming Platform

A StreamYard-like live streaming platform built with Laravel, PHP, and WebRTC technology. This is a functional proof of concept demonstrating multi-camera live streaming, video upload/playback, pre-recorded stream scheduling, and host/guest collaboration features.

## 🚀 Features

### Core Streaming Features
- **Multi-Camera Live Streaming**: Stream from multiple camera sources simultaneously
- **Camera Switching**: Switch between different camera feeds during live broadcast
- **Video Upload & Playback**: Upload video clips and play them during live streams
- **Pre-Recorded Stream Scheduling**: Schedule pre-recorded content to go live at specific times
- **Host/Guest Interface**: Support for multiple participants (host + guests) in streams
- **Real-Time Control Panel**: Start/stop streams, switch feeds, manage participants

### Technical Features
- **WebRTC Integration**: Real-time video communication
- **Laravel Broadcasting**: Real-time event broadcasting with Pusher
- **Laravel Sanctum**: API authentication
- **File Storage**: Video upload and management
- **Database Relationships**: Comprehensive data modeling for streams, participants, and videos

## 🛠 Technology Stack

- **Backend**: Laravel 12 (PHP 8.4+)
- **Database**: MySQL/PostgreSQL
- **Frontend**: Vanilla JavaScript with modern WebRTC APIs
- **Real-time**: Laravel Broadcasting with Pusher
- **Authentication**: Laravel Sanctum
- **File Storage**: Laravel's filesystem (local/public storage)

## 📋 Prerequisites

- PHP 8.4 or higher
- Composer
- MySQL or PostgreSQL
- Node.js and npm (for any additional frontend assets)

## ⚡ Installation & Setup

### 1. Clone and Install Dependencies

```bash
# Navigate to the project directory
cd streaming-platform

# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 2. Database Configuration

Update your `.env` file with database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=streaming_platform
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 3. Run Migrations

```bash
php artisan migrate
```

### 4. Configure Broadcasting (Pusher)

Update your `.env` file with Pusher credentials:

```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1
```

### 5. Configure Sanctum

Sanctum is used for API authentication. Make sure to add the Sanctum middleware to your API routes:

```php
// In routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    // Your protected API routes
});
```

### 6. Storage Configuration

Create storage link for file uploads:

```bash
php artisan storage:link
```

## 🎯 Usage Guide

### Creating a Stream

1. **Host creates a stream** via API:
```bash
curl -X POST /api/streams \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "My Live Stream",
    "description": "A great streaming session",
    "type": "live",
    "max_participants": 10
  }'
```

2. **Share the stream URL** with participants

### Joining a Stream

1. **Participants join** via API:
```bash
curl -X POST /api/streams/{stream_id}/join \
  -H "Authorization: Bearer YOUR_TOKEN"
```

2. **Access the stream interface** at `/streams/{stream_id}`

### Uploading Videos

1. **Upload video files** via API:
```bash
curl -X POST /api/videos/upload \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "video=@/path/to/video.mp4" \
  -F "title=Video Title" \
  -F "description=Video Description"
```

### Managing Participants

1. **Invite participants**:
```bash
curl -X POST /api/streams/{stream_id}/invite \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "participant@example.com",
    "role": "guest"
  }'
```

## 📁 Project Structure

```
streaming-platform/
├── app/
│   ├── Events/                 # Broadcasting events
│   ├── Http/Controllers/       # API controllers
│   ├── Models/                 # Eloquent models
│   └── Notifications/          # Email notifications
├── database/migrations/        # Database migrations
├── public/                     # Public assets
├── resources/views/           # Blade templates
├── routes/                    # Route definitions
├── storage/                   # File storage
└── config/                    # Configuration files
```

## 🔧 API Endpoints

### Stream Management
- `POST /api/streams` - Create new stream
- `GET /api/streams/{stream}` - Get stream details
- `POST /api/streams/{stream}/start` - Start stream
- `POST /api/streams/{stream}/stop` - Stop stream
- `POST /api/streams/{stream}/join` - Join stream
- `POST /api/streams/{stream}/leave` - Leave stream

### Video Management
- `POST /api/videos/upload` - Upload video
- `GET /api/videos` - List user's videos
- `GET /api/videos/{video}` - Get video details
- `DELETE /api/videos/{video}` - Delete video

### Participant Management
- `POST /api/streams/{stream}/invite` - Invite participant
- `GET /api/streams/{stream}/participants` - List participants
- `POST /api/streams/{stream}/participants/{participant}/accept` - Accept invitation
- `POST /api/streams/{stream}/participants/{participant}/decline` - Decline invitation

## 🎨 Frontend Interface

The streaming interface provides:

- **Main video display** with WebRTC integration
- **Camera selector** for switching between multiple cameras
- **Control panel** with start/stop buttons
- **Participants grid** showing active participants
- **Video library** for uploaded content
- **Real-time updates** via Laravel Echo

## 🔐 Authentication

The platform uses Laravel Sanctum for API authentication:

1. **Register/Login** users through Laravel's built-in authentication
2. **Generate API tokens** for authenticated requests
3. **Use tokens** in API requests for authorization

## 📡 Real-Time Features

Powered by Laravel Broadcasting and Pusher:

- **Stream status updates** (started, ended)
- **Participant join/leave** notifications
- **Real-time participant list** updates
- **Live stream events** broadcasting

## 🚀 Deployment

### Production Setup

1. **Configure production environment**:
```bash
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
```

2. **Set up SSL certificate** for secure WebRTC connections

3. **Configure Pusher** with production credentials

4. **Set up file storage** (AWS S3, DigitalOcean Spaces, etc.)

5. **Configure queue worker** for background processing:
```bash
php artisan queue:work
```

### Performance Optimization

- **Database indexing** on frequently queried columns
- **File compression** for video uploads
- **CDN integration** for video delivery
- **Caching strategies** for frequently accessed data

## 🔧 Development

### Running Locally

```bash
# Start Laravel development server
php artisan serve

# In another terminal, start queue worker
php artisan queue:work

# For real-time features, set up Pusher or Laravel WebSockets
```

### Testing

```bash
# Run PHPUnit tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
```

## 📝 Architecture Decisions

### Database Design
- **Streams** table for managing live and pre-recorded streams
- **StreamParticipants** for host/guest relationships
- **Videos** table for uploaded content management
- **StreamSchedules** for pre-recorded stream scheduling

### Real-Time Communication
- **Laravel Broadcasting** for server-side event dispatching
- **Pusher** for real-time client updates
- **Private channels** for stream-specific communications

### File Management
- **Laravel Storage** for file handling
- **Thumbnail generation** for video previews
- **Metadata extraction** for video information

## 🚧 Limitations & Future Enhancements

### Current Limitations
- Basic WebRTC implementation (no advanced streaming protocols)
- Local file storage only
- No advanced video processing features
- Limited to HTTP-based streaming

### Potential Enhancements
- **RTMP/ HLS streaming** for better performance
- **Cloud storage integration** (AWS S3, Cloudinary)
- **Advanced video processing** (transcoding, effects)
- **Mobile app development**
- **Analytics and reporting**
- **Monetization features**

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## 📄 License

This project is for educational/demonstration purposes. Please check with Next Era Media Group for any licensing requirements.

## 📞 Support

For technical questions or issues:
- Check Laravel documentation: https://laravel.com/docs
- Review Pusher documentation: https://pusher.com/docs
- Check WebRTC specifications: https://webrtc.org/

## 🎯 Evaluation Notes

This is a **proof of concept** demonstrating:
- Complex Laravel application architecture
- Real-time features implementation
- Database design and relationships
- API development with authentication
- Frontend integration with WebRTC
- File upload and management
- Event-driven programming

The codebase showcases modern PHP development practices, clean architecture, and the ability to build complex streaming solutions under time constraints.
