# Professional Email Template System

## Overview
This custom email template system provides professional, modern email layouts for your automated bot trading investment website. All emails use a consistent design with your site's branding and color scheme.

## Features
- ✅ **Professional Design** - Modern, responsive email layouts
- ✅ **Brand Consistency** - Uses primary colors from green.css theme
- ✅ **Mobile Responsive** - Optimized for all devices
- ✅ **Reusable Components** - Info boxes, buttons, tables, lists
- ✅ **Dynamic Content** - Pulls site info from settings table
- ✅ **User Currency** - Displays amounts in user's selected currency

## Base Layout
Location: `resources/views/emails/layout.blade.php`

The base layout includes:
- **Header** with logo/site name
- **Body** content area (@yield('content'))
- **Footer** with site info, address, contact email

### Color Scheme (from green.css)
- Primary: #0e4152 (Dark teal)
- Success: #49b336 (Green)
- Info: #00b8d9 (Cyan)
- Warning: #ffab00 (Orange)
- Danger: #ff5630 (Red)

## Available Email Templates

### 1. Welcome Email
**File:** `welcome.blade.php`
**Sent:** When new user registers
**Features:**
- Welcome message with success box
- 3-step getting started guide
- Platform benefits list
- CTA buttons for dashboard and support

### 2. Deposit Email
**File:** `success-deposit.blade.php`
**Sent:** When deposit is made or processed
**Versions:**
- Admin notification (when `$foramin = true`)
- Confirmed deposit (when `$deposit->status == 'Processed'`)
- Pending deposit (default)
**Features:**
- Transaction details table
- Processing timeline
- Next steps guide

### 3. Withdrawal Email
**File:** `withdrawal-status.blade.php`
**Sent:** When withdrawal is requested or processed
**Versions:**
- Admin notification (when `$foramin = true`)
- Completed withdrawal (when `$withdrawal->status == 'Processed'`)
- Pending withdrawal (default)
**Features:**
- Transaction summary
- Expected arrival times
- Track status button

### 4. ROI/Profit Email
**File:** `newroi.blade.php`
**Sent:** When trading bot generates profit
**Features:**
- Profit celebration message
- Profit details table
- Reinvestment suggestions
- Portfolio management link

### 5. Trading Bot Completion
**File:** `trading-bot-completion.blade.php`
**Sent:** When trading bot session completes
**Features:**
- Success/info box based on profit/loss
- Session summary with color-coded results
- Dashboard access button

### 6. Investment Plan End
**File:** `endplan.blade.php`
**Sent:** When investment plan expires
**Features:**
- Completion confirmation
- Capital return details
- Next action options (withdraw, reinvest, upgrade)

### 7. Custom Notification
**File:** `NewNotification.blade.php`
**Sent:** For admin-created custom notifications
**Features:**
- Dynamic salutation
- Image attachment support
- Custom action button
- Optional footer note

## Using the Email Templates

### Basic Usage
```php
// In your blade view
@extends('emails.layout')

@section('content')
    <h1>Your Email Title</h1>
    <p>Your email content here...</p>
@endsection
```

### Sending Emails
```php
// In your controller
Mail::send('emails.welcome', [
    'user' => $user,
    'settings' => $settings
], function($message) use ($user) {
    $message->to($user->email, $user->name)
            ->subject('Welcome to ' . config('app.name'));
});
```

## Available Components

### 1. Info Box (Blue)
```html
<div class="info-box">
    <p><strong>Important Information</strong></p>
    <p>Your message here.</p>
</div>
```

### 2. Success Box (Green)
```html
<div class="success-box">
    <p><strong>Success!</strong> Your action was completed.</p>
</div>
```

### 3. Warning Box (Yellow/Orange)
```html
<div class="warning-box">
    <p><strong>Warning:</strong> Please note this information.</p>
</div>
```

### 4. Details Table
```html
<table class="details-table">
    <tr>
        <td>Label</td>
        <td>Value</td>
    </tr>
</table>
```

### 5. Primary Button
```html
<div class="text-center">
    <a href="{{ $url }}" class="button">Button Text</a>
</div>
```

### 6. Secondary Button
```html
<div class="text-center">
    <a href="{{ $url }}" class="button button-secondary">Button Text</a>
</div>
```

### 7. Features List (with checkmarks)
```html
<ul class="features-list">
    <li>Feature one</li>
    <li>Feature two</li>
    <li>Feature three</li>
</ul>
```

### 8. Divider
```html
<div class="divider"></div>
```

### 9. Muted Text
```html
<p class="text-muted">Small print or footnote text</p>
```

### 10. Spacer
```html
<div class="spacer"></div>
```

## Required Variables

All email templates expect these variables:
- `$settings` - Settings object from database (contains site_name, logo, contact_email, site_address, address_o)
- `$user` - User object (contains name, email, currency)

## Settings Table Fields Used
- `site_name` - Company/site name
- `logo` - Path to logo image
- `contact_email` - Support email address
- `site_address` - Website URL
- `address_o` - Physical address

## Best Practices

1. **Always use user's currency**: `{{ $user->currency }}{{ number_format($amount, 2) }}`
2. **Use site_address for URLs**: `{{ $settings->site_address }}/user/dashboard`
3. **Format dates consistently**: `{{ $date->format('F j, Y g:i A') }}`
4. **Include security notes**: Add footer text for transaction verification
5. **Use appropriate boxes**: Success for positive actions, Warning for concerns, Info for neutral
6. **Center buttons**: Wrap buttons in `<div class="text-center">`
7. **Add spacing**: Use `<div class="spacer"></div>` or margin-top on paragraphs

## Customization

### Changing Colors
Edit `resources/views/emails/layout.blade.php` and update the CSS variables in the `:root` section or specific class styles.

### Adding New Components
Add new CSS classes in the `<style>` section of `layout.blade.php`.

### Creating New Email Templates
1. Create new blade file in `resources/views/emails/`
2. Start with `@extends('emails.layout')`
3. Add content in `@section('content')...@endsection`
4. Use available components for consistency

## Testing

### Preview Emails
Use Laravel's mail preview feature:
```php
Route::get('/preview-email', function () {
    $user = User::first();
    $settings = Settings::first();
    return view('emails.welcome', compact('user', 'settings'));
});
```

### Send Test Email
```php
php artisan tinker
Mail::raw('Test email', function($message) {
    $message->to('your@email.com')->subject('Test');
});
```

## Troubleshooting

**Issue:** Logo not showing
- Ensure `$settings->logo` path is correct
- Check if file exists in `storage/` directory
- Verify public storage link: `php artisan storage:link`

**Issue:** Styles not applying
- Email clients have limited CSS support
- Avoid using CSS classes that aren't inline or in `<style>` tags
- Test with multiple email clients (Gmail, Outlook, etc.)

**Issue:** Variables undefined
- Always pass `$settings` and `$user` when sending emails
- Check variable names match what's used in templates

## Migration from Laravel Default

If you were previously using `@component('mail::message')`:
1. Replace with `@extends('emails.layout')`
2. Change `@component('mail::button')` to `<a href="..." class="button">`
3. Wrap content in `@section('content')...@endsection`
4. Update `#` headers to `<h1>`, `<h2>` tags
5. Use component classes (info-box, success-box, etc.)

## Support

For issues or customization help, refer to:
- Laravel Mail Documentation: https://laravel.com/docs/mail
- Bootstrap Email Inspiration: https://bootstrapemail.com/
- Email Client CSS Support: https://www.caniemail.com/
