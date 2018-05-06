# Documentation

This document contains instructions for installing and using the source code, as well as general hints on the structure and design of the application.

## Development

To get started with development, run the first seven steps from the installation section (see below); but do note that in `data/config/elbo.yml`, `environment.phase` must be set to `development` instead of production.

The application is developed in a model-view-controller pattern. The application logic resides in `app/` and the views reside in `resources/views/`. The front controller, `public/index.php` invokes the correct controllers for incoming requests. Specifically, the controllers reside in `app/Controllers` and the models in `app/Models`. Protection to the resources provided by the controllers (such as CSRF protection) is provided by a set of middlewares, located in `app/Middlewares`.

The application makes use of dependency injection; the composition root can be found in `app/services.php`. One notable exception to this is the use of Eloquent, which uses static classes and must be bootstrapped globally with `bootstrap_eloquent(new \Elbo\Library\Configuration())`.

Assets are managed with the help of gulp, and the command `./node_modules/.bin/gulp watch` will watch for changes to assets and rebuild them on the fly.

Development should be performed through PHP's built-in web server, which can be run with `./bin/elbo-cli serve`. This will run the application at port 8000 on localhost; you can run it on any other port by specifying a port number as an argument.

In production, views, routes and configuration are cached and they need to be cleared after updating, this can be done by running `./bin/elbo-cli clean:all`.

## Installation

This application has been tested with PHP 7.2, nodejs 8.x and nginx >= 1.10.0, although it may run with lower versions. MySQL and Redis are required for the application to run.

The following instructions discuss installation on Debian (and Debian derived systems, such as Ubuntu).

* Run the following commands to install the core dependencies:

		sudo apt update
		sudo apt install git redis-server php-curl php-gd php-fpm php-gmp php-cli php-mbstring php-intl nginx nodejs npm
		which node || sudo ln -s /usr/bin/nodejs /usr/bin/node

* Install composer:

		wget https://getcomposer.org/installer
		php installer
		mkdir -p ~/.local/bin
		mv composer.phar ~/.local/bin
		echo 'export PATH="$PATH:$HOME/.local/bin"' >> ~/.bashrc
		. ~/.bashrc

* Clone the repository, and move to the cloned folder:

		git clone https://github.com/boolean-world/elbo.git
		cd elbo

* Pull in the PHP and JS dependencies:

		composer install
		npm install

* Copy `data/config_sample.yml` to `data/config.yml` and change the required values. Most importantly, you should set `environment.phase` to `production`, and insert values in the `api_key` section. In addition, you should create an user and a table in MySQL (if you haven't done so already), and fill in the corresponding values in the `database` section. You should also change the values under the `redis` section as necessary.

* Install the MaxMind GeoLite2 Country database.

		./bin/elbo-cli update:geoip

* Create the database tables:

		./bin/elbo-cli migrations:install

* Build the assets:

		./node_modules/.bin/gulp

* Set the ownership of the files to the user under which the webserver runs:

		sudo chown -R www-data: .

* Add/edit a file in `/etc/nginx/sites-available/*` to serve the website. The minimal configuration is as follows:

		server {
			listen 80;
			listen [::]:80;

			root /path/to/elbo/public;

			location /~qr/files {
				alias /path/to/elbo/data/tmp/qr;

				internal;
				expires 1d;
				add_header Pragma public;
				add_header Cache-Control "public";
			}

			location ~ ^/assets/ {
				expires 1d;
				add_header Pragma public;
				add_header Cache-Control "public";
			}

			location / {
				include fastcgi_params;
				fastcgi_pass unix:/run/php/php7.0-fpm.sock;
				fastcgi_param SCRIPT_FILENAME /path/to/elbo/public/index.php;
			}
		}

Elbo should be now up and running! Have a look at the usage section for further instructions.

## Usage

After installation, you may want to create an administrator account. The administrator is allowed to block links/websites and manage users. You can create one by executing:

	./bin/elbo-cli create-admin

Once you log in, the administration panel will be available at `http://<your_server_name>/~admin`.

If you want to block known malicious websites, you can do this by running:

	./bin/elbo-cli update:policies

(You can set up a cron job that does this automatically once a day.)

If you want to block users using disposable email addresses from registering to the service, you can do so by running:

	./bin/elbo-cli update:dispemail
