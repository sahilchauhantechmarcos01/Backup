# WordPress Multi-Site Sync Plugin 
Contributors: Sahil
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A powerful plugin that synchronizes posts, pages, custom post types of different categories between multiple WordPress sites using secure REST API communication.

# Description 

This plugin provides a complete multi-site content synchronization system.  
It allows you to connect multiple external WordPress sites and pull content from them into your main site.  

All communication is protected by an API key and custom REST endpoints.

**Features:**
- Sync Posts / Pages / All post types
- Sync from specific taxonomy
- Sync from specific category
- Add max pazination size
- API-key protected REST endpoints
- Displays SUCCESS or FAILURE per remote site

**Main Page**
- Add website URL
- Add API key
- Website list will be displayed

**Sync Page**
- Choose the settings 
- sync the sites clicking SYNC button

# Installation 

1. Upload the plugin files to `/wp-content/plugins/`
2. Activate the plugin through the "Plugins" menu in WordPress
3. Go to **Dashboard → WP SYNC REST**

# How It Works 

Each remote website must have this plugin installed and an API key generated.  
The main WordPress site initiates all syncing operations.


The plugin uses custom REST API endpoints located at:

`/wp-json/sync-api/v1/`







