

CREATE TABLE `orders` (
  `orders_id` int(11) NOT NULL,
  `quanlity` int(11) NOT NULL,
  `totalamount` int(11) NOT NULL,
  `User_ID` int(11) NOT NULL,
  `Product_ID` int(11) NOT NULL,
  `order date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `products` (
  `id_product` int(11) NOT NULL,
  `products_name` varchar(255) NOT NULL,
  `totalquanlity` int(11) NOT NULL,
  `quantitySold` int(11) NOT NULL,
  `descs` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `images` varchar(255) NOT NULL,
  `image1` varchar(255) NOT NULL,
  `image2` varchar(255) NOT NULL,
  `dates` datetime NOT NULL,
  `status` enum('Còn hàng','Hết hàng') NOT NULL DEFAULT 'Còn hàng'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `products` (`id_product`, `products_name`, `totalquanlity`, `quantitySold`, `descs`, `price`, `images`, `image1`, `image2`, `dates`, `status`) VALUES
(1, 'Tết An Nhiên', 100, 50, 'Người Việt có thói quen mời nhau 1 ly, mở đầu cho lời chúc xuân. Vì thế, hộp quà Tết giờ đây cũng không thể thiếu chai vang ngon. Với tên gọi “Tết An Nhiên”, Giỏ quà Tết Việt xin gửi đến quý khách combo quà tết gồm một chai Vang và hai loại hạt được nhập khẩu trực tiếp từ Úc, một loại trái cây sấy nhập khẩu trực tiếp từ Mỹ. ', 1100000.00, 'https://demo037030.web30s.vn/datafiles/32835/upload/images/products/tet-an-nhien-1.jpg', '', '', '2025-09-29 14:24:36', 'Còn hàng'),
(2, 'Tết Phú Quý', 100, 50, 'Hộp quà Tết Phú Quý có chất lượng đẳng cấp gồm Vang Chile, hạt Dẻ Cười Mỹ, hạt Macca Úc, Nho Khô Nguyên Cành Úc. Tất cả sản phẩm được nhập khẩu trực tiếp từ các nước tiên tiến nhất Thế Giới.\r\n\r\nGiữa hàng vạn những lựa chọn quà biếu tết, hộp quà Tết Phú Quý chính là sự lựa chọn lý tưởng cho doanh nghiệp. Với ý nghĩa gửi tặng những điều tốt đẹp nhất, hứa hẹn một năm mới kinh doanh phát đạt, phát triển thịnh vượng, Tết Phú Quý gồm một Vang nhập khẩu trực tiếp từ Chile, hạt Dẻ Cười Mỹ, hạt Macca Úc, Nho Khô Nguyên Cành Úc sẽ mang đến lời tri ân sâu sắc đến sếp, đối tác, nhân viên… trong dịp lễ tết xuân về.', 1250000.00, 'https://demo037030.web30s.vn/datafiles/32835/upload/images/products/tet-phu-quy.jpg', '', '', '2025-09-29 14:25:57', 'Còn hàng'),
(3, 'Tết Hưng Thịnh', 100, 60, 'Set quà gồm một chai Vang và hạt dinh dưỡng, trái cây sấy nhập khẩu trực tiếp được đóng gói đặt trong vỏ hộp da màu vàng sang trọng, thích hợp tri ân khách hàng thân thiết, cấp trên, gia đình… mang ý nghĩa tấn tài tấn lộc …\r\n\r\nVới thông điệp, “Tết Hưng Thịnh – Đón lộc vào nhà”, Giỏ quà Tết Việt cho ra đời combo quà Tết “Hưng Thịnh” gồm một chai Vang, nho khô nguyên cành từ Mỹ, hạt Óc Chó và Macca được nhập khẩu trực tiếp. Các sản phẩm được đóng gói trong vỏ hộp da viền vàng sang trọng mang ý nghĩa tấn tài tấn lộc, thể hiện sự đẳng cấp và sang trọng của người tặng.', 1250000.00, 'https://demo037030.web30s.vn/datafiles/32835/upload/images/products/tui-qua-tet-hung-thinh-1.jpg', '', '', '2025-09-29 14:27:38', 'Còn hàng'),
(4, 'Tết Thịnh Vượng', 100, 20, 'Combo gồm một chai Vang SantaHill Cabernet Sauvingnon và bốn loại hạt nhập khẩu cao cấp từ các quốc gia hàng đầu thế giới. Tết Thịnh Vượng rất thích hợp biếu tặng Ông bà, Cha Mẹ, đối tác kinh doanh nhân dịp tết nguyên đán 2021.\r\n\r\nĐược thiết kế bắt mắt, đẹp đẽ và sự kết hợp của các sản phẩm tốt cho sức khỏe, hộp quà Tết “Thịnh Vượng” sẽ là một món quà đầy hữu ích và ý nghĩa cho ngày Tết dành tặng cho những người thân yêu, cấp trên, đối tác và nhân viên. Quà Tết Thịnh Vượng gồm một chai Vang SantaHill Cabernet Sauvingnon, hai loại hạt dinh dưỡng và hai loại trái cây sấy cao cấp. Tất cả sản phẩm đều được nhập khẩu trực tiếp từ các quốc gia hàng đầu thế giới.', 1250000.00, 'https://demo037030.web30s.vn/datafiles/32835/upload/images/products/tui-qua-tet-thinh-vuong-1.jpg', '', '', '2025-09-29 14:29:08', 'Còn hàng'),
(5, 'Giỏ quà tết 15', 100, 100, 'Tết đến Xuân về là ngày mà những người con xa nhà trở về sum vầy với gia đình sau một năm bận rộn. Đây cũng là dịp mà mỗi người có cơ hội thể hiện tình cảm với những người thân yêu, gia đình, đồng nghiệp hay bạn bè với những lời chúc mang đầy ý nghĩa.\r\n\r\nHiểu được điều này, Giỏ quà Tết Sum Vầy tại Giỏ quà Tết Việt chứa đựng thông điệp dành riêng cho những người con xa xứ, những đứa con xa quê, mong họ có thể được Sum Vầy bên người thân của mình trong dịp Tết Bính Ngọ này, đơn giản nhưng chứa đựng ý nghĩa chân thành.\r\n\r\nVới cách trình bày trang trọng, đa dạng về sản phẩm, chất lượng được khẳng định thông qua những thương hiệu uy tín trên thị trường, Giỏ quà Tết Sum Vầy sẽ cùng bạn có một năm mới thật bình an và hạnh phúc. ', 2950000.00, 'https://demo037030.web30s.vn/datafiles/32835/upload/images/products/gio-qua-tet-15.jpg', '', '', '2025-09-29 17:43:40', 'Hết hàng');


CREATE TABLE `user` (
  `ID_user` int(11) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Username` varchar(100) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Phone` varchar(25) NOT NULL,
  `Password` varchar(100) NOT NULL,
  `Address` varchar(255) NOT NULL,
  `roles` varchar(255) NOT NULL,
  `money` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



INSERT INTO `user` (`ID_user`, `Name`, `Username`, `Email`, `Phone`, `Password`, `Address`, `roles`, `money`) VALUES
(1, 'Trần Minh Quang', 'Quang2512', 'Cohoi2512@gmail.com', '0352143569', 'quang123', 'Bắc Ninh', 'User', 1000000000),
(2, 'Lê Toàn Diện', 'Dien123', 'Dien20004@gmail.com', '0398436663', 'Dien456', 'Hà Nội', 'user', 1000000000),
(3, 'Phạm Văn Trường', 'Truong123', 'Truong2004@gmail.com', '0398436663', 'Truong456', 'Ninh Bình', 'user', 1000000000),
(4, 'Nguyễn Văn Tú', 'Tus123', 'Tu2003@gmail.com', '0373027069', 'Tu123', 'Hà Nội', 'admin', 1000000000),
(5, 'Nguyễn Văn Hùng', 'hung678', 'Hung2004@gmail.com', '0985123768', 'Hung678', 'Lâm Đồng', 'admin', 1000000000);


ALTER TABLE `orders`
  ADD PRIMARY KEY (`orders_id`),
  ADD KEY `FOREIGN KEY REFERENCES` (`Product_ID`),
  ADD KEY `null` (`User_ID`);


ALTER TABLE `products`
  ADD PRIMARY KEY (`id_product`);


ALTER TABLE `user`
  ADD PRIMARY KEY (`ID_user`);


ALTER TABLE `orders`
  MODIFY `orders_id` int(11) NOT NULL AUTO_INCREMENT;

-
ALTER TABLE `products`
  MODIFY `id_product` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;


ALTER TABLE `user`
  MODIFY `ID_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;


ALTER TABLE `orders`
  ADD CONSTRAINT `FOREIGN KEY REFERENCES` FOREIGN KEY (`Product_ID`) REFERENCES `products` (`id_product`),
  ADD CONSTRAINT `null` FOREIGN KEY (`User_ID`) REFERENCES `user` (`ID_user`);
COMM