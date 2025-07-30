# 🚀 Railway Deployment Guide for PHP MCP Server

## Quick Railway Deployment

### 1. Connect Your GitHub Repository

1. Go to [Railway.app](https://railway.app)
2. Click "New Project"
3. Select "Deploy from GitHub repo"
4. Choose your `MCP-PHP` repository

### 2. Environment Configuration

Railway will automatically detect it's a PHP project. The deployment files are already configured:

- ✅ `Procfile` - Railway start command
- ✅ `railway.json` - Build configuration  
- ✅ `start.sh` - Server startup script
- ✅ `composer.json` - PHP dependencies

### 3. Environment Variables (Optional)

In Railway dashboard, you can set:
```
PORT=8080 (auto-set by Railway)
MCP_DEBUG=false
LOG_LEVEL=info
```

### 4. Deploy

Railway will automatically:
1. Install dependencies: `composer install --no-dev`
2. Start the server: `bash start.sh`
3. Expose your app on a public URL

## 🌐 Your Railway URLs

After deployment, you'll get:
- **Web Interface**: `https://your-app.railway.app/`
- **MCP Endpoint**: `https://your-app.railway.app/mcp`

## ⚙️ VS Code Configuration

Update your `mcp.json`:

```json
{
  "servers": {
    "railway-mcp-server": {
      "type": "http",
      "url": "https://your-app.railway.app/mcp"
    }
  }
}
```

## 🔧 Troubleshooting Railway

### Common Issues:

1. **Build Fails**: 
   - Check PHP version (8.1+ required)
   - Verify `composer.json` is valid

2. **Server Won't Start**:
   - Check Railway logs for errors
   - Ensure `start.sh` has execute permissions

3. **MCP Endpoint Not Working**:
   - Test: `https://your-app.railway.app/mcp`
   - Check CORS headers in browser

### Railway Logs

View logs in Railway dashboard or CLI:
```bash
railway logs
```

## 🎉 Success!

Once deployed, your MCP server will be available 24/7 on Railway with all your tools:
- 🧮 Calculator
- ⏰ DateTime  
- 🔍 Web Search
- 👋 Multilingual Greetings
- 📚 Story Generation

---

**Railway Deployment Complete! 🚂**
