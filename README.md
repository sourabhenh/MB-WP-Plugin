# Mastery Box WordPress Plugin

An interactive WordPress plugin game where users fill out a form, proceed to a game page featuring several gift boxes, pick one, and instantly discover if they've won a prize.

## Features

### Frontend User Journey
1. **Form Submission** - Users are presented with a customizable form (fields set by admin)
2. **Game Interaction** - Users are redirected to a game page with animated gift boxes
3. **Result Feedback** - Instant results with win/lose messages and prize information

### Admin Backend Functionality
- **Gift Management** - Add, edit, remove gifts with win percentages
- **Form Configuration** - Customizable form fields and validation
- **Analytics Dashboard** - View statistics, entry data, and gift distribution
- **Settings Panel** - Configure game behavior and messages

## Installation

1. Upload the `mastery-box` folder to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure your gifts and settings in the admin panel
4. Use the shortcodes to display the game on your pages

## Shortcodes

### Form Shortcode
```
[masterybox_form]
```
Display the entry form where users submit their information.

Optional parameters:
- `redirect_url` - URL to redirect to after form submission

### Game Shortcode
```
[masterybox_game]
```
Display the game interface with gift boxes.

Optional parameters:
- `boxes` - Number of boxes to display (overrides global setting)

## Usage Example

1. Create a page called "Enter Contest" and add: `[masterybox_form redirect_url="/play-game"]`
2. Create a page called "Play Game" and add: `[masterybox_game]`
3. Configure your gifts in the admin panel
4. Start collecting entries!

## Database Tables

The plugin creates two custom tables:
- `wp_masterybox_gifts` - Stores gift information and win percentages
- `wp_masterybox_entries` - Stores user entries and game results

## Security Features

- Nonce verification for all forms
- Input sanitization and validation
- SQL injection prevention
- XSS protection

## Customization

The plugin includes CSS classes for easy customization:
- `.mastery-box-form-container` - Form styling
- `.mastery-box-game-container` - Game area styling
- `.mastery-box` - Individual box styling
- `.gift-quality-[quality]` - Gift quality badges

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher

## Support

For support and documentation, visit the plugin settings page in your WordPress admin.

## Changelog

### 1.0.0
- Initial release
- Form builder functionality
- Interactive game interface
- Admin dashboard and analytics
- Gift management system
- Responsive design
- Security features
