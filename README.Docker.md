### Services

Start MySQL, PostgreSQL, RustFS, and imgproxy:

```bash
make up
```

From the host, MySQL is on `localhost:33060`, PostgreSQL on `localhost:54320`, RustFS S3 on `http://localhost:9010`, the RustFS console on `http://localhost:9011`, and imgproxy on `http://localhost:8081`. Inside Compose they stay on `mysql:3306`, `postgres:5432`, `rustfs:9000`, and `imgproxy:8080`. Database name, user, and password are `media` (`root` / `root` for the MySQL root account). RustFS credentials are `MEDIAUPLOAD` / `media-s3-secret`, and the bucket used by the S3 test is `media`. Override the host ports with `MYSQL_PUBLISH_PORT`, `POSTGRES_PUBLISH_PORT`, `RUSTFS_PUBLISH_PORT`, `RUSTFS_CONSOLE_PUBLISH_PORT`, and `IMGPROXY_PUBLISH_PORT`.

### Tests

SQLite, inside the PHP image:

```bash
make test
```

The same suite against MySQL and PostgreSQL on the Compose network:

```bash
make test-databases
```

Upload an image to RustFS and resize it with imgproxy over S3:

```bash
make test-s3
```
