# PDF Generation with Browserless.io

This application now uses `spatie/laravel-pdf` with Browserless.io for PDF generation, eliminating the need for Node.js on the server.

## Setup Instructions

### 1. Get Browserless.io API Key

1. Go to [Browserless.io](https://www.browserless.io/)
2. Sign up for a free account (includes 1000 free API calls per month)
3. Get your API key from the dashboard

### 2. Configure Environment Variables

Add these to your `.env` file:

```env
# Browserless.io Configuration for PDF Generation
BROWSERLESS_API_KEY=your_api_key_here
BROWSERLESS_URL=https://chrome.browserless.io
```

### 3. Test PDF Generation

- Go to Super Admin Panel → Reports → Salary Reports
- Click "Download" on any salary slip
- The PDF should be generated using Browserless.io

## How It Works

- **Local Development**: If Browserless credentials are not configured, it will try to use local Browsershot (requires Node.js)
- **Production**: With Browserless.io configured, PDFs are generated remotely without needing Node.js on your server

## Benefits

✅ No Node.js installation required on hosting server  
✅ No Chrome/Chromium installation needed  
✅ Works on shared hosting (like Hostinger)  
✅ Fast and reliable PDF generation  
✅ Free tier includes 1000 API calls/month  

## Fallback

If Browserless.io is not configured, the system will attempt to use local Browsershot. Make sure you have:
- Node.js installed
- Puppeteer installed (`npm install puppeteer`)

## Configuration

The PDF configuration is in `config/laravel-pdf.php`. You can customize:
- PDF format (A4, Letter, etc.)
- Margins
- Background rendering
- And more...
