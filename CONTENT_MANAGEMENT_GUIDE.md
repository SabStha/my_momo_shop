# Content Management System Guide

## Overview
The new Content Management System provides a user-friendly interface to manage all hardcoded content across your web and mobile applications. It replaces the old table-based interface with an intuitive card-based dashboard.

## Features

### 🎯 **Platform-Specific Management**
- **All Content**: View content for all platforms
- **Web App**: Content specific to the web application
- **Mobile App**: Content specific to the mobile application

### 🔍 **Advanced Filtering & Search**
- **Search**: Find content by title, key, or description
- **Filter by Section**: Home, Auth, Menu, Admin, General, Bulk
- **Filter by Type**: Text, HTML, Image, JSON, Boolean

### ⚡ **Quick Actions**
- **Quick Edit**: Edit content inline without leaving the dashboard
- **Toggle Status**: Enable/disable content with one click
- **Delete**: Remove content with confirmation

### 📱 **Visual Interface**
- **Card Layout**: Easy-to-scan content cards
- **Platform Badges**: Clear visual indicators for platform-specific content
- **Status Indicators**: Active/Inactive status at a glance
- **Content Previews**: See content without opening edit forms

## How to Use

### 1. Access the Dashboard
- Go to **Admin Panel** → **Content Management**
- Or visit: `http://your-domain.com/admin/site-content/dashboard`

### 2. Navigate Content
- **Switch Platforms**: Use the tabs at the top (All, Web App, Mobile App)
- **Search**: Type in the search box to find specific content
- **Filter**: Use the dropdown filters to narrow down results

### 3. Add New Content
- Click **"Add Content"** button
- Fill in the form:
  - **Title**: Human-readable name
  - **Key**: Unique identifier (auto-generated from title)
  - **Platform**: All, Web App, or Mobile App
  - **Section**: Where this content appears
  - **Component**: Specific part of the section
  - **Type**: Text, HTML, Image, JSON, or Boolean
  - **Content**: The actual content value

### 4. Edit Content
- **Quick Edit**: Click "Quick Edit" on any card for inline editing
- **Full Edit**: Click "Full Edit" for complete form editing

### 5. Manage Content Status
- Click the **Active/Inactive** button to toggle content status
- Inactive content won't appear in your applications

## Content Types

### 📝 **Text**
Plain text content for titles, descriptions, etc.
```
Example: "Welcome to Momo Shop"
```

### 🌐 **HTML**
HTML markup for rich content
```html
Example: <div class="hero"><h1>Welcome</h1><p>Shop now!</p></div>
```

### 🖼️ **Image**
URL to an image file
```
Example: /images/hero-background.jpg
```

### 📊 **JSON**
Structured data for complex content
```json
Example: {"categories": [{"id": 1, "name": "Electronics"}]}
```

### ✅ **Boolean**
True/False values for feature flags
```
Example: true (for enabling features)
```

## Sections & Components

### 🏠 **Home**
- **Hero**: Main banner content
- **Categories**: Featured categories
- **Banner**: Promotional banners

### 🔐 **Auth**
- **Title**: Login/register page titles
- **Button**: Button text for forms

### 📋 **Menu**
- **Title**: Menu page titles
- **Empty**: Empty state messages

### ⚙️ **Admin**
- **Title**: Admin panel titles
- **Dashboard**: Dashboard-specific content

### 🌐 **General**
- **Feature Flags**: Boolean toggles
- **Global Settings**: App-wide settings

## Best Practices

### 1. **Naming Convention**
- Use descriptive titles: "Home Hero Title" instead of "Title1"
- Use snake_case for keys: "home_hero_title"

### 2. **Platform Separation**
- Use "All" for content that appears on both platforms
- Use "Web App" for desktop-specific content
- Use "Mobile App" for mobile-specific content

### 3. **Content Organization**
- Group related content in the same section
- Use components to further organize within sections
- Set appropriate sort orders for display sequence

### 4. **Testing**
- Always test content changes in both web and mobile apps
- Use the preview feature to verify content before publishing
- Toggle content status to test different states

## API Usage

### Get Content by Key
```php
// Get specific content
$title = SiteContent::getContent('home_hero_title', 'Default Title', 'web');

// Get all content for a section
$homeContent = SiteContent::getBySection('home', 'web');

// Get content as key-value array
$homeArray = SiteContent::getSectionAsArray('home', 'web');
```

### Set Content Programmatically
```php
SiteContent::setContent(
    'welcome_message',
    'Welcome Message',
    'Welcome to our shop!',
    'text',
    'home',
    'hero',
    'all',
    'Main welcome message for homepage'
);
```

## Troubleshooting

### Content Not Appearing
1. Check if content is **Active**
2. Verify **Platform** setting matches your app
3. Ensure **Section** and **Component** are correct
4. Check if content is being cached (clear cache if needed)

### Quick Edit Not Working
1. Ensure you're logged in as admin
2. Check browser console for JavaScript errors
3. Verify CSRF token is present

### Search/Filter Issues
1. Clear search box and try again
2. Reset filters to "All"
3. Refresh the page

## Migration from Old System

The new system is fully compatible with existing content. All your current content will automatically appear in the new dashboard with the same functionality.

## Support

For issues or questions about the Content Management System:
1. Check this guide first
2. Review the sample content for examples
3. Contact your development team

---

**Happy Content Managing! 🎉**
