# Run PHPUnit in Docker across PHP 8.4/8.5 and Laravel 12/13.
#
#   make test
#   make test PHP_VERSIONS=8.4 LARAVEL_VERSIONS=13
#   make test ARGS="--filter MediaTest"
#   make analyse
#   make baseline
#   make pint
#   make pint ARGS="--test"
#   make up
#   make test-databases
#   make test-s3

IMAGE ?= turahe-media
COMPOSE ?= docker compose
NETWORK ?= turahe-media
PHP_VERSION ?= 8.4
PHP_VERSIONS ?= 8.4 8.5
LARAVEL ?= 12
LARAVEL_VERSIONS ?= 12 13
SCRIPT ?= test
ARGS ?=
DB_CONNECTION ?=
DB_HOST ?=
DB_PORT ?=
DB_DATABASE ?=
DB_USERNAME ?=
DB_PASSWORD ?=
IMGPROXY_URL ?=
IMGPROXY_KEY ?=
IMGPROXY_SALT ?=
IMGPROXY_SOURCE_URL ?=
AWS_ACCESS_KEY_ID ?= MEDIAUPLOAD
AWS_SECRET_ACCESS_KEY ?= media-s3-secret
AWS_DEFAULT_REGION ?= us-east-1
AWS_BUCKET ?= media
AWS_ENDPOINT ?=
AWS_USE_PATH_STYLE_ENDPOINT ?= true

.PHONY: test analyse baseline pint build test-one up test-databases test-s3

test:
	@set -eu; \
	for php in $(PHP_VERSIONS); do \
		$(MAKE) build PHP_VERSION=$$php; \
		for laravel in $(LARAVEL_VERSIONS); do \
			$(MAKE) test-one PHP_VERSION=$$php LARAVEL=$$laravel; \
		done; \
	done

analyse: build
	$(MAKE) test-one SCRIPT=analyse LARAVEL=$(LARAVEL)

baseline: build
	$(MAKE) test-one SCRIPT=baseline LARAVEL=$(LARAVEL)

pint: build
	$(MAKE) test-one SCRIPT=pint LARAVEL=$(LARAVEL)

build:
	docker build --build-arg PHP_VERSION=$(PHP_VERSION) -t $(IMAGE):php$(PHP_VERSION) .

up:
	$(COMPOSE) up -d --wait mysql postgres rustfs imgproxy

test-s3: up build
	$(MAKE) test-one LARAVEL=$(LARAVEL) \
		IMGPROXY_URL=http://imgproxy:8080 \
		AWS_ENDPOINT=http://rustfs:9000 \
		AWS_ACCESS_KEY_ID=$(AWS_ACCESS_KEY_ID) \
		AWS_SECRET_ACCESS_KEY=$(AWS_SECRET_ACCESS_KEY) \
		AWS_DEFAULT_REGION=$(AWS_DEFAULT_REGION) \
		AWS_BUCKET=$(AWS_BUCKET) \
		AWS_USE_PATH_STYLE_ENDPOINT=true \
		ARGS="--filter ImgproxyS3Test"

test-databases: up build
	$(MAKE) test-one LARAVEL=$(LARAVEL) DB_CONNECTION=mysql DB_HOST=mysql DB_PORT=3306 DB_DATABASE=media DB_USERNAME=media DB_PASSWORD=media IMGPROXY_URL=http://imgproxy:8080
	$(MAKE) test-one LARAVEL=$(LARAVEL) DB_CONNECTION=pgsql DB_HOST=postgres DB_PORT=5432 DB_DATABASE=media DB_USERNAME=media DB_PASSWORD=media IMGPROXY_URL=http://imgproxy:8080

test-one:
	@set -eu; \
	case "$(LARAVEL)" in \
		12) testbench=10 ;; \
		13) testbench=11 ;; \
		*) echo "Unsupported Laravel $(LARAVEL). Use 12 or 13." >&2; exit 1 ;; \
	esac; \
	network_args=""; \
	output_args=""; \
	source_args=""; \
	if [ "$(DB_CONNECTION)" = "mysql" ] || [ "$(DB_CONNECTION)" = "pgsql" ] || [ -n "$(AWS_ENDPOINT)" ]; then \
		network_args="--network $(NETWORK)"; \
	fi; \
	if [ "$(SCRIPT)" = "baseline" ]; then \
		output_args="-v $(CURDIR):/out"; \
	fi; \
	if [ "$(SCRIPT)" = "pint" ]; then \
		source_args="-v $(CURDIR)/src:/var/www/html/src -v $(CURDIR)/tests:/var/www/html/tests -v $(CURDIR)/config:/var/www/html/config"; \
	fi; \
	printf '==> PHP %s / Laravel %s / %s' "$(PHP_VERSION)" "$(LARAVEL)" "$(SCRIPT)"; \
	if [ -n "$(DB_CONNECTION)" ]; then printf ' / %s' "$(DB_CONNECTION)"; fi; \
	printf ' (orchestra/testbench ^%s.0)\n' "$$testbench"; \
	docker run --rm $$network_args $$output_args $$source_args \
		-e DB_CONNECTION="$(DB_CONNECTION)" \
		-e DB_HOST="$(DB_HOST)" \
		-e DB_PORT="$(DB_PORT)" \
		-e DB_DATABASE="$(DB_DATABASE)" \
		-e DB_USERNAME="$(DB_USERNAME)" \
		-e DB_PASSWORD="$(DB_PASSWORD)" \
		-e IMGPROXY_URL="$(IMGPROXY_URL)" \
		-e IMGPROXY_KEY="$(IMGPROXY_KEY)" \
		-e IMGPROXY_SALT="$(IMGPROXY_SALT)" \
		-e IMGPROXY_SOURCE_URL="$(IMGPROXY_SOURCE_URL)" \
		-e AWS_ACCESS_KEY_ID="$(AWS_ACCESS_KEY_ID)" \
		-e AWS_SECRET_ACCESS_KEY="$(AWS_SECRET_ACCESS_KEY)" \
		-e AWS_DEFAULT_REGION="$(AWS_DEFAULT_REGION)" \
		-e AWS_BUCKET="$(AWS_BUCKET)" \
		-e AWS_ENDPOINT="$(AWS_ENDPOINT)" \
		-e AWS_USE_PATH_STYLE_ENDPOINT="$(AWS_USE_PATH_STYLE_ENDPOINT)" \
		"$(IMAGE):php$(PHP_VERSION)" bash -lc "\
		composer require \"laravel/framework:^$(LARAVEL).0\" \"orchestra/testbench:^$${testbench}.0\" --no-interaction --no-update --no-scripts && \
		composer update --prefer-dist --no-interaction --no-progress && \
		composer $(SCRIPT) -- $(ARGS); status=\$$?; \
		if [ \"$(SCRIPT)\" = baseline ] && [ -f phpstan-baseline.neon ]; then \
			cp phpstan-baseline.neon /out/phpstan-baseline.neon; \
			chown $(shell id -u):$(shell id -g) /out/phpstan-baseline.neon; \
		fi; \
		if [ \"$(SCRIPT)\" = pint ]; then \
			chown -R $(shell id -u):$(shell id -g) src tests config; \
		fi; \
		exit \$$status"
