# Shopify Vue Starter Kit

A production-ready, multi-tenant Shopify app starter built with **Laravel 12**, **Vue 3**, and **Tailwind CSS v4**. Get your Shopify app up and running in minutes with OAuth, webhooks, billing, and a complete product management example.

## ✨ Features

### Backend (Laravel 12 API)
- **Shopify OAuth Integration** - Seamless app installation and authentication
- **Multi-tenant Architecture** - Multiple shops can install your app
- **Product CRUD API** - Full example implementation with authorization
- **Webhook Processing** - Queued handling for Shopify events (products, app uninstall)
- **Billing/Subscriptions** - Ready-to-use endpoints for recurring charges
- **Form Validation** - Request classes with custom error messages
- **Policy Authorization** - Shop-based access control
- **API Resources** - Clean, consistent JSON responses

### Frontend (Vue 3 SPA)
- **Vue 3 + Composition API** - Modern, reactive UI
- **Tailwind CSS v4** - Utility-first styling with dark mode support
- **Vue Router** - Client-side routing
- **Pinia** - Intuitive state management
- **Responsive Design** - Mobile-first, works on all devices
- **Complete CRUD Example** - Product management (list, create, edit, delete)
- **Dashboard** - Overview with shop details and quick actions

### Developer Experience
- **Database Seeders** - Pre-populated with 25 test products
- **Model Factories** - Realistic fake data generation
- **Testing Ready** - Pest v4 configured for feature and browser tests
- **Laravel Pint** - Code formatting built-in
- **Development Scripts** - One command to run everything
- **Well Documented** - Clear code comments and TODO markers

### Shopify CLI Integration
- **Shopify CLI Support** - Pre-configured for app and extension development
- **Theme App Extension Example** - Product rating block included
- **Extension Generation** - Easy scaffolding for new extensions
- **One-Command Deploy** - Deploy app and extensions together

## 📋 Requirements

- **PHP** 8.3+
- **Composer** 2.x
- **Node.js** 18+ and NPM
- **SQLite** (or MySQL/PostgreSQL)
- **Shopify Partner Account** - [Create one here](https://partners.shopify.com/)

## 🚀 Quick Start

### 1. Clone and Install

```bash
# Clone the repository
git clone https://github.com/yourusername/shopify-vue-starter.git
cd shopify-vue-starter

# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 2. Database Setup

```bash
# Create SQLite database
touch database/database.sqlite

# Run migrations and seed test data
php artisan migrate:fresh --seed
```

This creates a test shop (`test-shop.myshopify.com`) with 25 sample products.

### 3. Configure Shopify App

#### Create App in Shopify Partners

1. Go to [Shopify Partners](https://partners.shopify.com/)
2. Click **Apps** → **Create app**
3. Choose **Custom app** or **Public app**
4. Fill in your app details

#### Get API Credentials

In your Shopify Partner dashboard:
1. Navigate to your app
2. Go to **App setup**
3. Copy your **API key** and **API secret key**

#### Configure Environment

Update your `.env` file:

```env
# Shopify Configuration
SHOPIFY_APP_NAME="Your App Name"
SHOPIFY_API_KEY=your_api_key_here
SHOPIFY_API_SECRET=your_api_secret_here
SHOPIFY_API_SCOPES="read_products,write_products,read_orders"
SHOPIFY_API_VERSION=2024-01
SHOPIFY_BILLING_ENABLED=true
SHOPIFY_BILLING_TEST=true
```

#### Set App URLs in Shopify

In your Shopify Partner app configuration:

- **App URL**: `https://your-domain.com`
- **Allowed redirection URL(s)**: `https://your-domain.com/authenticate`

For local development with ngrok:
```bash
ngrok http 8000
# Use the https URL provided by ngrok
```

### 4. Start Development

**Standard Development (Laravel + Vite):**
```bash
# Start all services (Laravel, queue worker, Vite, logs)
composer run dev
```

This runs:
- Laravel development server at `http://localhost:8000`
- Queue worker for processing webhooks
- Vite dev server with hot module reloading at port `5173`
- Laravel Pail for real-time logs

**With Shopify Extensions:**

When working on extensions, run Shopify CLI in a separate terminal:

```bash
# Terminal 1 - Laravel + Vite
composer run dev

# Terminal 2 - Shopify CLI (extensions with hot reload)
npm run shopify:dev
```

**Note:** Shopify CLI uses port 3000 (no conflict with Vite on 5173 or Laravel on 8000)

**Alternative**: Run services separately
```bash
# Terminal 1 - Laravel server
php artisan serve

# Terminal 2 - Queue worker
php artisan queue:listen

# Terminal 3 - Vite dev server
npm run dev
```

### 5. Access Your App

Visit `http://localhost:8000` - you'll see your Vue SPA dashboard with the seeded products!

## 🛠️ Shopify CLI Setup (Optional)

The Shopify CLI allows you to develop app extensions, manage app configurations, and deploy to Shopify.

### Installation

**Option 1: Global Installation (Recommended)**
```bash
npm install -g @shopify/cli
```

**Option 2: Use Project Dependency**
The CLI is already in package.json devDependencies:
```bash
npm install
npm run shopify -- <command>
```

### Link Your App

Before using the CLI, link your project to your Shopify Partner app:

```bash
# If installed globally
shopify app config link

# Or via npm
npm run shopify:config
```

This will:
1. Prompt you to select your Partner organization
2. Select or create an app
3. Update shopify.app.toml with your app's client ID

### Verify Setup

```bash
npm run shopify:info
```

## 🎨 Working with Extensions

### Included Example: Theme App Extension

This starter includes a product rating block as an example theme app extension.

**Location:** `extensions/theme-app-extension/`

**Features:**
- Product rating block with customizable settings
- Reusable star rating snippet
- Localization support

### Developing Extensions

**Start Extension Dev Server:**
```bash
npm run shopify:dev
```

This command:
- Starts a development server with hot reloading
- Creates a tunnel to localhost
- Pushes the extension to your development store
- Provides a preview link

**Test the Extension:**
1. Run `npm run shopify:dev`
2. Select your development store
3. Navigate to Online Store > Themes > Customize
4. Add the "Product Rating" block (found in Apps section)
5. Changes to extension code will hot reload

### Creating New Extensions

```bash
npm run extension:create
```

Choose from:
- Theme app extension
- Checkout UI extension
- Admin action
- And more...

### Deploying Extensions

```bash
npm run shopify:deploy
```

This creates a new app version. You must then release it in the Partner dashboard for changes to reach production users.

## 📁 Project Structure

```
shopify-vue-starter/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/              # API controllers
│   │   │   │   ├── ProductController.php  # Product CRUD example
│   │   │   │   ├── ShopController.php
│   │   │   │   └── BillingController.php
│   │   │   ├── ShopifyController.php      # OAuth handling
│   │   │   └── WebhookController.php      # Webhook receiver
│   │   ├── Requests/             # Form validation
│   │   └── Resources/            # API response formatting
│   ├── Jobs/
│   │   ├── ProcessWebhook.php    # Webhook processing
│   │   └── RegisterWebhooks.php
│   ├── Models/
│   │   ├── User.php              # Shop model (multi-tenant)
│   │   ├── Product.php
│   │   └── WebhookLog.php
│   └── Policies/
│       └── ProductPolicy.php     # Authorization logic
├── database/
│   ├── factories/                # Fake data generators
│   ├── migrations/
│   └── seeders/
├── extensions/                   # Shopify app extensions
│   └── theme-app-extension/     # Example theme extension
│       ├── assets/
│       ├── blocks/
│       │   └── product-rating.liquid
│       ├── snippets/
│       │   └── star-rating.liquid
│       ├── locales/
│       │   └── en.default.json
│       ├── shopify.extension.toml
│       └── README.md
├── resources/
│   ├── js/
│   │   ├── components/           # Vue components (when needed)
│   │   ├── stores/               # Pinia stores
│   │   │   ├── shop.js
│   │   │   └── products.js
│   │   ├── views/                # Vue pages
│   │   │   ├── Dashboard.vue
│   │   │   └── products/
│   │   │       ├── Index.vue     # Product list
│   │   │       ├── Create.vue
│   │   │       └── Edit.vue
│   │   ├── router/
│   │   │   └── index.js          # Route definitions
│   │   ├── App.vue               # Root component
│   │   └── app.js                # Vue entry point
│   ├── css/
│   │   └── app.css               # Tailwind imports
│   └── views/
│       └── app.blade.php         # SPA shell
├── routes/
│   ├── api.php                   # API endpoints
│   └── web.php                   # OAuth & webhooks
├── config/
│   └── shopify-app.php           # Shopify configuration
└── shopify.app.toml              # Shopify CLI app config
```

## 🔧 Configuration

### Shopify Settings

Edit `config/shopify-app.php` to customize:

- **API Version**: Update `api_version` when Shopify releases new versions
- **OAuth Scopes**: Modify `api_scopes` based on your app's needs
- **Billing Plans**: Configure `billing_plans` array for subscriptions
- **Webhooks**: Add webhook topics in the `webhooks` array

Example billing configuration:

```php
'billing_plans' => [
    [
        'name' => 'Basic Plan',
        'price' => 9.99,
        'interval' => 'every_30_days',
        'trial_days' => 7,
    ],
    [
        'name' => 'Pro Plan',
        'price' => 29.99,
        'interval' => 'every_30_days',
        'trial_days' => 14,
    ],
],
```

### Environment Variables

Key `.env` settings:

```env
# Application
APP_URL=http://localhost:8000

# Session (required for Shopify embedded apps)
SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=none

# Queue (for webhook processing)
QUEUE_CONNECTION=database

# Shopify
SHOPIFY_APP_NAME="Your App Name"
SHOPIFY_API_KEY=your_key
SHOPIFY_API_SECRET=your_secret
SHOPIFY_API_SCOPES="read_products,write_products"
SHOPIFY_API_VERSION=2024-01
SHOPIFY_BILLING_ENABLED=true
SHOPIFY_BILLING_TEST=true  # Set to false in production
```

## 🎯 Usage Guide

### Adding New Features

#### 1. Create a New Model

```bash
php artisan make:model Order -mf
```

#### 2. Add API Routes

In `routes/api.php`:

```php
Route::middleware(['auth.shopify'])->prefix('v1')->group(function () {
    Route::apiResource('orders', OrderController::class);
});
```

#### 3. Create Controller

```bash
php artisan make:controller Api/OrderController --api
```

#### 4. Add Authorization

```bash
php artisan make:policy OrderPolicy --model=Order
```

#### 5. Create Vue Components

```bash
# Create views directory
mkdir -p resources/js/views/orders

# Add your Vue components
touch resources/js/views/orders/Index.vue
touch resources/js/views/orders/Create.vue
```

### Webhook Handling

Webhooks are automatically queued and processed. To add custom webhook handling:

1. Edit `app/Jobs/ProcessWebhook.php`
2. Add your topic to the `match` statement:

```php
match ($this->log->topic) {
    'orders/create' => $this->handleOrdersCreate(),
    'products/create' => $this->handleProductsCreate(),
    // Add more...
}
```

### Syncing with Shopify

The starter includes placeholder methods for syncing data with Shopify's API:

```php
// In ProductController
public function syncWithShopify(Request $request, Product $product)
{
    // TODO: Implement Shopify API sync
    // Use the Shopify API client from the laravel-shopify package
}
```

**Example implementation:**

```php
use Osiset\ShopifyApp\Services\ShopifyApp;

public function syncWithShopify(Request $request, Product $product, ShopifyApp $shopify)
{
    $shop = $request->user();

    $response = $shopify->setShop($shop)->rest(
        'POST',
        '/admin/api/2024-01/products.json',
        [
            'product' => [
                'title' => $product->title,
                'body_html' => $product->description,
                'variants' => [
                    ['price' => $product->price]
                ]
            ]
        ]
    );

    $product->update([
        'shopify_product_id' => $response['product']['id']
    ]);

    return ProductResource::make($product->fresh());
}
```

## 🧪 Testing

### Run Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/ProductApiTest.php

# Run tests with coverage
php artisan test --coverage
```

### Create New Tests

```bash
# Feature test
php artisan make:test --pest OrderApiTest

# Browser test (Pest v4)
php artisan make:test --pest Browser/CheckoutFlowTest
```

### Example Test

```php
it('returns only products for authenticated shop', function () {
    $shopA = User::factory()->create();
    $shopB = User::factory()->create();

    Product::factory()->count(3)->for($shopA, 'shop')->create();
    Product::factory()->count(2)->for($shopB, 'shop')->create();

    $response = $this->actingAs($shopA)
        ->getJson('/api/v1/products');

    $response->assertSuccessful()
        ->assertJsonCount(3, 'data');
});
```

## 🏗️ Building for Production

### 1. Build Assets

```bash
npm run build
```

### 2. Optimize Laravel

```bash
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 3. Environment Setup

Update `.env` for production:

```env
APP_ENV=production
APP_DEBUG=false
SHOPIFY_BILLING_TEST=false
SESSION_SECURE_COOKIE=true
```

### 4. Queue Worker

Use a process manager like Supervisor to keep your queue worker running:

```bash
php artisan queue:work --tries=3
```

## 🔒 Security Best Practices

1. **Never commit `.env`** - Contains sensitive API keys
2. **Use HTTPS** - Required for Shopify OAuth and embedded apps
3. **Validate Webhooks** - The package handles HMAC verification automatically
4. **Scope Permissions** - Only request Shopify API scopes you need
5. **Rate Limiting** - Implement API rate limiting for your endpoints
6. **CSRF Protection** - Enabled by default for non-webhook routes

## 🐛 Troubleshooting

### OAuth Redirect Issues

**Error**: "Redirect URI does not match"

**Solution**: Ensure your `.env` `APP_URL` matches the URL configured in Shopify Partners exactly (including protocol).

### Webhook Not Processing

**Error**: Webhooks logged but not processed

**Solution**: Make sure your queue worker is running:
```bash
php artisan queue:listen
```

### CSRF Token Mismatch

**Error**: 419 CSRF token mismatch on API calls

**Solution**: Check that your `SESSION_SAME_SITE=none` and `SESSION_SECURE_COOKIE=true` in `.env`

### Vue Components Not Loading

**Error**: Blank page or Vue errors in console

**Solution**:
1. Rebuild assets: `npm run build`
2. Clear cache: `php artisan cache:clear`
3. Check browser console for errors

### Shopify CLI Issues

**Error**: "No shopify.app.toml found"

**Solution**: Run `npm run shopify:config` to create/update the configuration file.

---

**Error**: "Extension not appearing in theme editor"

**Solution**:
1. Ensure `npm run shopify:dev` is running
2. Refresh the theme editor
3. Check that you selected the correct development store

---

**Error**: "SHOPIFY_API_KEY not found"

**Solution**: Run `npm run shopify:config` to populate shopify.app.toml with the correct client ID from your Partner app.

---

**Error**: "API version mismatch"

**Solution**: Ensure `api_version` in `shopify.app.toml` matches the version in `config/shopify-app.php` (should be `2024-04`).

## 📚 Additional Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Vue 3 Documentation](https://vuejs.org/)
- [Shopify App Development](https://shopify.dev/docs/apps)
- [Shopify CLI Documentation](https://shopify.dev/docs/api/shopify-cli)
- [Theme App Extensions](https://shopify.dev/docs/apps/build/online-store/theme-app-extensions)
- [kyon147/laravel-shopify Package](https://github.com/Kyon147/laravel-shopify)
- [Tailwind CSS v4](https://tailwindcss.com/)
- [Pinia Documentation](https://pinia.vuejs.org/)

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 💖 Acknowledgments

- Built with [Laravel 12](https://laravel.com)
- Shopify integration via [kyon147/laravel-shopify](https://github.com/Kyon147/laravel-shopify)
- Styled with [Tailwind CSS v4](https://tailwindcss.com)
- Powered by [Vue 3](https://vuejs.org)

---

**Happy Building! 🎉** If this starter kit helps you build amazing Shopify apps, consider giving it a ⭐ on GitHub!
