# Production Deployment Checklist

## Pre-Deployment

### 1. Environment Configuration
- [ ] Copy `.env.example` to `.env`
- [ ] Set `CI_ENVIRONMENT = production`
- [ ] Configure database credentials
- [ ] Set unique `encryption.key` (32 characters)
- [ ] Configure email settings
- [ ] Set `app.baseURL` to production domain
- [ ] Set `cookie.secure = true` (for HTTPS)

### 2. Database Setup
- [ ] Create production database
- [ ] Import schema from `database/` folder
- [ ] Run any pending migrations: `php spark migrate`
- [ ] Test database connection

### 3. File Permissions
```bash
chmod -R 755 writable/
chmod -R 755 public/uploads/
```

### 4. Web Server Configuration
- [ ] Configure Apache/Nginx virtual host
- [ ] Set document root to `public/` folder
- [ ] Enable URL rewriting
- [ ] Configure HTTPS certificate
- [ ] Set up proper error pages

### 5. Security Settings
- [ ] Disable PHP error display in production
- [ ] Enable firewall rules
- [ ] Configure backup strategy
- [ ] Set up monitoring

## Post-Deployment

### 1. Testing Checklist
- [ ] Test public pages load correctly
- [ ] Test admin login functionality
- [ ] Test news creation/editing
- [ ] Test file uploads
- [ ] Test all forms and validation

### 2. Performance Optimization
- [ ] Enable PHP OPcache
- [ ] Configure caching
- [ ] Optimize images
- [ ] Minify CSS/JS if needed

### 3. Monitoring Setup
- [ ] Configure error logging
- [ ] Set up uptime monitoring
- [ ] Configure backup automation
- [ ] Set up log rotation

## Important Notes

### Development Routes
The following routes are **automatically disabled** in production:
- `/dev/login-as-admin` - Returns 404
- All other `/dev/*` routes

### Sensitive Files
These files are protected by `.gitignore` and should never be committed:
- `.env` - Environment configuration
- `writable/logs/*` - Log files
- `writable/session/*` - Session files
- `writable/cache/*` - Cache files

### Database Credentials
Never commit database credentials to version control. Always use environment variables.

### File Uploads
Ensure the `public/uploads/` and `writable/uploads/` directories are writable but not accessible via direct URL browsing.

## Troubleshooting

### Common Issues

1. **404 Errors**
   - Check `app.baseURL` in `.env`
   - Verify URL rewriting is enabled
   - Check file permissions

2. **Database Connection**
   - Verify database credentials in `.env`
   - Check database server is running
   - Test with `php spark db:show`

3. **Session Issues**
   - Check `writable/session/` permissions
   - Verify session configuration
   - Clear session files: `rm -rf writable/session/*`

4. **Upload Issues**
   - Check upload directory permissions
   - Verify PHP upload limits
   - Check disk space

## Maintenance

### Regular Tasks
- Update CodeIgniter framework: `composer update`
- Clear caches: `php spark cache:clear`
- Rotate logs: Remove old log files
- Backup database and files

### Security Updates
- Monitor security advisories
- Update dependencies regularly
- Review access logs
- Update encryption keys periodically
