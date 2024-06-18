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
