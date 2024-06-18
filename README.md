# INSTALL
```
composer install
```
- Create .env and go to my.payos.vn. After that, Please add your payment-gateway key in .env
- Go to Haravan and create new HaravanToken which allow to read and write order
- In Haravan admin, go to this path "./admin/settings/checkouts" and add this script in "Xu li don hang"
```


<script src="https://dev.hara.payos.vn/checkout.js"></script>

``` 

# RUN APP LOCAL
```
 php -S localhost:8000 -t public
```

- In folder public create file .htaccess
```
RewriteEngine On
RewriteRule ^checkout\.js$ checkout.php [L]

# Chuyển hướng tất cả các yêu cầu khác đến `index.php`
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [QSA,L]

```