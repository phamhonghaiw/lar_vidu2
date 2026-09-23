# Deploy lar_vidu2 lên Render + Aiven

## Cấu hình đã chuẩn bị

- `Dockerfile`: PHP 8.2 FPM, Nginx, extensions, Composer dependencies từ `composer.lock`; image chạy không chứa Composer hoặc thư viện phát triển của dự án.
- `.dockerignore`: bỏ `.env`, code Git, dependencies local, dữ liệu SQLite, chứng chỉ, log và cache khỏi build context.
- `docker/entrypoint.sh`: kiểm tra cấu hình, cache Laravel, migrate, kiểm tra cấu hình server và quản lý hai tiến trình Nginx/PHP-FPM. Lỗi ở bất kỳ bước chuẩn bị nào sẽ dừng khởi động.
- Session và cache của bản Docker lưu ở MySQL. Migration mới tạo bảng `sessions`; không đổi `.env` local.
- Nginx phục vụ duy nhất thư mục `public`, lắng nghe `PORT` và chuyển log ra Render.
- Giao diện hiện dùng CDN/inline CSS/JS, không có `@vite`, nên chưa cần Node trong image. Nếu chuyển sang Vite, cần thêm bước `npm ci && npm run build` và commit `package-lock.json`.

## 1. Chuẩn bị database Aiven

Tạo dịch vụ MySQL Free, lấy host, port, database, username, password từ Aiven Console. Không dùng `localhost` hoặc mặc định port 3306 thay cho thông số Aiven cung cấp.

Tải chứng chỉ CA của dịch vụ. Trên Render thêm **Secret File** tên `ca.pem`, rồi đặt `MYSQL_ATTR_SSL_CA=/etc/secrets/ca.pem`. Chứng chỉ phải đọc được bởi tiến trình PHP-FPM. Cấu hình MySQL giữ xác minh chứng chỉ bật; không tắt xác minh để né lỗi kết nối.

Entry point sao chép CA lúc container khởi động sang `/run/app-certificates/mysql-ca.pem`, cấp quyền đọc cho `www-data`, rồi kiểm tra định dạng PEM bằng chính user này trước khi cache/migrate. Cách này xử lý trường hợp Secret File chỉ đọc được bởi root. Trên Render vẫn giữ `MYSQL_ATTR_SSL_CA=/etc/secrets/ca.pem`; đường dẫn nội bộ được script tự điều chỉnh, chứng chỉ không được ghi vào image khi build. Kiểm tra PEM không chứng minh CA thuộc đúng dịch vụ: bước kết nối MySQL vẫn xác minh chứng chỉ máy chủ.

Database mới sẽ được tạo bảng bằng migration lúc khởi động. Migration không tự chuyển tài khoản, sản phẩm, đơn hàng từ máy lên Aiven. Nếu cần dữ liệu hiện có, xuất/import SQL trước lần chạy đầu, bao gồm bảng `migrations`, và kiểm tra schema tương thích. Không chạy `migrate:fresh` với dữ liệu cần giữ.

## 2. Tạo Web Service trên Render

1. Đưa source của riêng `lar_vidu2` lên GitHub. Kiểm tra các controller/view mới đang untracked đã được commit đầy đủ; không commit `.env` hoặc mật khẩu.
2. Chọn **New > Web Service**, kết nối repo, chọn **Docker** và gói **Free**.
3. Dockerfile Path: `./Dockerfile`; Docker Build Context: `.`. Nếu repo chứa nhiều dự án, đặt Root Directory là thư mục `lar_vidu2` trong repo.
4. Để Docker Command trống để dùng entrypoint của image.
5. Health Check Path: `/up`. Đây là kiểm tra Laravel khởi động được, không phải kiểm tra MoMo/GHN/email hoạt động.
6. Nhập các biến trong `docker/render.env.example`, thay placeholder và bổ sung Secret File CA.

Tạo `APP_KEY` một lần bằng lệnh sau trên máy và lưu kết quả vào biến môi trường Render:

```powershell
php artisan key:generate --show
```

Lệnh này chỉ in key, không ghi đè `.env`. Giữ nguyên key qua các lần deploy. Nếu đang chuyển dữ liệu đã mã hóa từ môi trường cũ, sử dụng key tương ứng thay vì tạo key mới.

`TRUSTED_PROXIES=*` dành cho dịch vụ nằm sau proxy của Render; bỏ biến này hoặc dùng danh sách IP/CIDR cụ thể khi chạy trực tiếp trên máy chủ khác. `APP_URL` phải là URL HTTPS thật. Biến môi trường và key chỉ được dùng khi container chạy, không truyền bí mật vào Docker build.

`RUN_MIGRATIONS=true` phù hợp bản demo một instance: chỉ áp dụng migration chưa chạy, không xóa dữ liệu. Có thể đặt `false` nếu đã chạy migration riêng. Khi mở rộng nhiều instance, chuyển migration sang một bước release riêng để tránh chạy đồng thời.

## 3. Email, MoMo và GHN

Ứng dụng gửi email xác thực khi đăng ký. `MAIL_MAILER=log` trong file mẫu chỉ phục vụ demo, **không gửi email đến người dùng**. Render Free chặn SMTP cổng 25/465/587, nên cấu hình Gmail hiện tại không dùng được. Trước khi mở đăng ký thực tế, cần chọn dịch vụ gửi qua HTTPS, cài dependency Laravel tương ứng, rồi cấu hình API key và địa chỉ gửi đã xác minh. Không dùng `log` để coi chức năng email đã hoàn thành.

Giữ `GHN_VERIFY_SSL=true` và `MOMO_VERIFY_SSL=true`. File mẫu dùng môi trường thử nghiệm; không trộn token thử nghiệm với endpoint thật. Đăng ký/cập nhật các URL trên dịch vụ tương ứng:

- MoMo redirect: `https://YOUR-SERVICE.onrender.com/payment/momo/callback`
- MoMo IPN: `https://YOUR-SERVICE.onrender.com/payment/momo/ipn`
- GHN webhook: `https://YOUR-SERVICE.onrender.com/ghn/webhook`

Render Free có thể ngủ sau 15 phút không có truy cập. Lần gọi đầu sau đó có thể chậm, kể cả webhook; phải thử thanh toán sandbox và cập nhật trạng thái vận chuyển trên host trước khi coi tích hợp hoạt động.

## 4. Kiểm tra image khi có Docker

```powershell
docker build -t lar-vidu2 .
docker run --rm -p 10000:10000 --env-file .env.docker --mount "type=bind,source=C:\path\to\ca.pem,target=/etc/secrets/ca.pem,readonly" -e APP_URL=http://localhost:10000 -e SESSION_SECURE_COOKIE=false lar-vidu2
```

Tự tạo `.env.docker` từ mẫu, điền cấu hình database thử nghiệm và thay đường dẫn CA thật. File này phải giữ ở máy và đã được ignore. Chạy container sẽ áp dụng migration vào database đã chỉ định nếu `RUN_MIGRATIONS=true`.

Kiểm tra `/up`, trang chủ, đăng nhập, giỏ hàng và admin. Khởi động lại container rồi kiểm tra session/giỏ hàng còn được giữ. Kiểm tra logs không có lỗi DB, permission hoặc cache. Thử email xác thực và giao dịch sandbox sau khi đã cấu hình dịch vụ.

File tạo trong container không bền vững trên Render Free. Dữ liệu nghiệp vụ/session/cache được giữ ở Aiven; nếu thêm upload ảnh/tệp, cần storage bên ngoài. Database mới chưa có tài khoản admin: import dữ liệu phù hợp hoặc cấp quyền admin cho tài khoản của bạn bằng quy trình quản trị riêng; image không tạo tài khoản/mật khẩu mặc định.

## Tài liệu đối chiếu

- [Docker trên Render](https://render.com/docs/docker)
- [Giới hạn Render Free](https://render.com/docs/free)
- [Kết nối PHP với Aiven MySQL](https://aiven.io/docs/products/mysql/howto/connect-with-php)
- [Laravel trusted proxies](https://laravel.com/docs/12.x/requests#configuring-trusted-proxies)
