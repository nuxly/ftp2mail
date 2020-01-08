# Ftp2Mail
> This is a robot that checks for files in FTP folder and sends an email with a download link when finds one.


![](header.png)

## Installation

OS X & Linux:

```sh
git clone https://github.com/nuxly/ftp2mail.git
cd ftp2mail
make install
```

## Usage example

After modifying your own configuration file from the ``sample-config.json`` file, run the following command:

```sh
php index.php --config your-config.json
```

To display program help, run the following command :

```sh
php index.php -h
```

You can create a cron job with the following command :

```sh
crontab -l | { cat; echo "* * * * * php /your-ftp2mail-directory/index.php --config config.json"; } | crontab -
```
## Meta

Lionel Vinceslas – [@Nuxly](https://twitter.com/nuxly) – contact@nuxly.com

Distributed under the CeCILL license. See ``LICENSE`` for more information.

[https://github.com/nuxly/](https://github.com/nuxly/)

## Contributing

1. Fork it (<https://github.com/yourname/ftp2mail/fork>)
2. Create your feature branch (`git checkout -b feature/fooBar`)
3. Commit your changes (`git commit -am 'Add some fooBar'`)
4. Push to the branch (`git push origin feature/fooBar`)
5. Create a new Pull Request
