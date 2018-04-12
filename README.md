# nempay-eccube2

## Overview
EC-CUBE 2 series Nem (Xem) payment plug-in.
By installing this plug-in you can settle in Xem.

## Demo

[Demo](http://nem-ec.tech/eccube2/)

## Install
1. Create plug-in file
```bash
$ git clone git@github.com:maroemon58/nempay-eccube2.git
$ cd nempay-eccube2
$ tar -zcvf NemPay.tar.gz *
```

2. Install on EC-CUBE
Install the created plug-in(NemPay.tar.gz) from "owner's store > plugin setting"

3. Plug-in setting
Register an auctioneer account (deposit destination)
**※ When testing, switch "Environment switch" to test environment(testnet)**

4. Payment confirmation setting
Set up confirmation program to activate payment every fixed time
Program：〜/NemPay/script/paymentconfirm.php
```bash
$ crontab -e
*/5 * * * * cd /var/www/html/eccube-2.13.5/data/downloads/plugin/NemPay/script; php /var/www/html/eccube-2.13.5/data/downloads/plugin/NemPay/script/paymentconfirm.php;
```

## Licence

[GNU](https://github.com/maroemon58/nempay-eccube2/blob/master/LICENSE)

## Author

[maroemon58](https://github.com/maroemon58)