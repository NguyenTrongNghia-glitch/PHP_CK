-- =====================================================
-- CSDL cho web "Hiệu Sách Nhỏ"
-- Chạy file này trong MySQL Workbench (tia sét hoặc Ctrl+Shift+Enter)
--
-- LƯU Ý: file xóa rồi tạo lại 5 bảng bên dưới trong database test_php
-- (users, categories, books, orders, order_items).
-- Dữ liệu cũ trong 5 bảng này (ví dụ tài khoản đăng ký thử) sẽ mất.
-- Không ảnh hưởng đến database hay bảng khác.
-- =====================================================
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS test_php
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE test_php;

DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS books;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

-- 1. Người dùng (register.php / login.php / profile.php / admin/users.php)
CREATE TABLE users (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  full_name   VARCHAR(100) NOT NULL,
  email       VARCHAR(100) NOT NULL UNIQUE,
  password    VARCHAR(255) NOT NULL,            -- mã băm password_hash(), không lưu mật khẩu thô
  phone       VARCHAR(20),
  address     VARCHAR(255),
  role        ENUM('user','admin') NOT NULL DEFAULT 'user',
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 2. Thể loại sách
CREATE TABLE categories (
  id    INT AUTO_INCREMENT PRIMARY KEY,
  name  VARCHAR(100) NOT NULL UNIQUE
);

-- 3. Sách
CREATE TABLE books (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  category_id  INT NULL,
  title        VARCHAR(200) NOT NULL,
  author       VARCHAR(100) NOT NULL,
  price        DECIMAL(10,0) NOT NULL DEFAULT 0,
  image        VARCHAR(500) NOT NULL,            -- đường dẫn / link ảnh bìa
  description  TEXT,
  featured     TINYINT(1) NOT NULL DEFAULT 0,    -- 1 = sách nổi bật
  created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- 4. Đơn hàng
CREATE TABLE orders (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  user_id     INT NOT NULL,
  receiver    VARCHAR(100) NOT NULL,             -- tên người nhận (lấy từ tài khoản lúc đặt)
  phone       VARCHAR(20)  NOT NULL,
  address     VARCHAR(255) NOT NULL,
  total       DECIMAL(12,0) NOT NULL DEFAULT 0,
  status      VARCHAR(30) NOT NULL DEFAULT 'Chờ xác nhận',
                                                 -- Chờ xác nhận / Đang giao / Hoàn thành / Đã hủy
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 5. Chi tiết đơn hàng
-- title và price lưu lại đúng lúc mua, nên sau này sửa giá hoặc xóa sách
-- thì đơn cũ vẫn hiển thị đúng.
CREATE TABLE order_items (
  id        INT AUTO_INCREMENT PRIMARY KEY,
  order_id  INT NOT NULL,
  book_id   INT NULL,
  title     VARCHAR(200) NOT NULL,
  quantity  INT NOT NULL DEFAULT 1,
  price     DECIMAL(10,0) NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (book_id)  REFERENCES books(id)  ON DELETE SET NULL
);

-- =====================================================
-- DỮ LIỆU MẪU (lấy từ includes/data.php cũ)
-- =====================================================
INSERT INTO categories (id, name) VALUES
(1, 'Văn học'),
(2, 'Kỹ năng sống'),
(3, 'Kinh tế'),
(4, 'Thiếu nhi'),
(5, 'Tâm lý');

INSERT INTO books (id, category_id, title, author, price, image, description, featured) VALUES
(1, 1, 'Nhà Giả Kim', 'Paulo Coelho', 79000,
 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?w=600',
 'Câu chuyện về hành trình theo đuổi ước mơ và khám phá ý nghĩa cuộc sống.', 1),
(2, 2, 'Tuổi Trẻ Đáng Giá Bao Nhiêu', 'Rosie Nguyễn', 85000,
 'https://images.unsplash.com/photo-1512820790803-83ca734da794?w=600',
 'Những chia sẻ về học tập, trải nghiệm và phát triển bản thân.', 1),
(3, 5, 'Đắc Nhân Tâm', 'Dale Carnegie', 95000,
 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?w=600',
 'Những nguyên tắc giao tiếp và xây dựng mối quan hệ tốt đẹp.', 1),
(4, 4, 'Dế Mèn Phiêu Lưu Ký', 'Tô Hoài', 55000,
 'https://images.unsplash.com/photo-1519682337058-a94d519337bc?w=600',
 'Cuộc phiêu lưu thú vị cùng những bài học về tình bạn.', 1),
(5, 2, 'Cà Phê Cùng Tony', 'Tony Buổi Sáng', 75000,
 'https://images.unsplash.com/photo-1516979187457-637abb4f9353?w=600',
 'Những câu chuyện thực tế về học tập và làm việc.', 0),
(6, 1, 'Bố Già', 'Mario Puzo', 120000,
 'https://images.unsplash.com/photo-1513001900722-370f803f498d?w=600',
 'Tác phẩm nổi tiếng về gia đình, quyền lực và cuộc sống.', 0),
(7, 3, 'Nghĩ Giàu Và Làm Giàu', 'Napoleon Hill', 99000,
 'https://images.unsplash.com/photo-1519682337058-a94d519337bc?w=600',
 'Những nguyên tắc tư duy và mục tiêu trong cuộc sống.', 0),
(8, 5, 'Tâm Lý Học Thành Công', 'Carol S. Dweck', 110000,
 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?w=600',
 'Khám phá tư duy phát triển và cách vượt qua thử thách.', 0);

-- Tài khoản quản trị mẫu: admin@gmail.com / 123456
INSERT INTO users (full_name, email, password, role) VALUES
('Quản trị viên', 'admin@gmail.com',
 '$2y$10$ofnsnCYTZiDzz0JxgHh0ROWMpKV1eK1h8ZhTQp7XxdmUP9DZFjE6K', 'admin');
