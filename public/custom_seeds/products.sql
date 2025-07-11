INSERT INTO `product_details` (`id`, `type`, `parent_id`, `name`, `status`, `created_at`, `updated_at`) VALUES
(1, 'parent', 'ROOT', 'Chemicals', 1, '2021-05-07 04:53:56', NULL),
(2, 'parent', '1', 'Pretreatment', 1, '2021-05-07 04:53:56', NULL),
(3, 'parent', '2', 'Scouring & Bleaching', 1, '2021-05-07 04:53:56', NULL),
(4, 'parent', '3', 'Sequestering Agent', 1, '2021-05-07 04:53:56', NULL),
(5, 'child', '3', 'Wetting Agent', 1, '2021-05-07 04:53:56', NULL),
(6, 'child', '3', 'De-aerating', 1, '2021-05-07 04:53:56', NULL),
(7, 'child', '3', 'Peroxide stabilizer', 1, '2021-05-07 04:53:56', NULL),
(8, 'child', '3', 'Anti Crease', 1, '2021-05-07 04:53:56', NULL),
(9, 'child', '3', 'De-foamer', 1, '2021-05-07 04:53:56', NULL),
(10, 'parent', '2', 'Dyeing', 1, '2021-05-07 04:53:56', NULL),
(11, 'parent', '10', 'Dyeing', 1, '2021-05-07 04:53:56', NULL),
(12, 'child', '11', 'Sequestering Agent/ Water Sofener', 1, '2021-05-07 04:53:56', NULL),
(13, 'child', '11', 'Reactive Levelling Agent', 1, '2021-05-07 04:53:56', NULL),
(14, 'child', '11', 'Disperse Levelling Agent', 1, '2021-05-07 04:53:56', NULL),
(15, 'child', '11', 'Dispersing Agent', 1, '2021-05-07 04:53:56', NULL),
(16, 'child', '11', 'Dye Bath Conditioner', 1, '2021-05-07 04:53:56', NULL),
(17, 'child', '11', 'Alkali Substitute', 1, '2021-05-07 04:53:56', NULL),
(18, 'parent', '10', 'Neutralisation', 1, '2021-05-07 04:53:56', NULL),
(19, 'child', '18', 'Core Alkali Neutralizer', 1, '2021-05-07 04:53:56', NULL),
(20, 'child', '18', 'Eco Acid', 1, '2021-05-07 04:53:56', NULL),
(21, 'parent', '10', 'Soaping', 1, '2021-05-07 04:53:56', NULL),
(22, 'child', '21', 'Neutral Washing Off', 1, '2021-05-07 04:53:56', NULL),
(23, 'child', '21', 'Acidic Washing Off', 1, '2021-05-07 04:53:56', NULL),
(24, 'parent', '10', 'Fixation', 1, '2021-05-07 04:53:56', NULL),
(25, 'child', '24', 'Washing Fastness', 1, '2021-05-07 04:53:56', NULL),
(26, 'child', '24', 'Wet Rubbiing Fastness', 1, '2021-05-07 04:53:56', NULL),
(27, 'parent', '2', 'Finishing', 1, '2021-05-07 04:53:56', NULL),
(28, 'parent', '27', 'Moisture Management', 1, '2021-05-07 04:53:56', NULL),
(29, 'child', '28', 'Anti Static/ Wicking', 1, '2021-05-07 04:53:56', NULL),
(30, 'parent', '27', 'Softening', 1, '2021-05-07 04:53:56', NULL),
(31, 'child', '30', 'Cationic Softeners', 1, '2021-05-07 04:53:56', NULL),
(32, 'child', '30', 'Silicone Softeners', 1, '2021-05-07 04:53:56', NULL),
(33, 'child', '30', 'Yarn Lubricant', 1, '2021-05-07 04:53:56', NULL),
(34, 'parent', 'ROOT', 'Enzymes', 1, '2021-05-07 04:53:56', NULL),
(35, 'parent', '34', 'Pretreatment', 1, '2021-05-07 04:53:56', NULL),
(36, 'parent', '35', 'Desizing', 1, '2021-05-07 04:53:56', NULL),
(37, 'child', '36', 'Desizing Enyme', 1, '2021-05-07 04:53:56', NULL),
(38, 'parent', '35', 'Scouring & Bleaching', 1, '2021-05-07 04:53:56', NULL),
(39, 'child', '38', 'Enzymatic Scouring', 1, '2021-05-07 04:53:56', NULL),
(40, 'child', '38', 'Low Temperature Scouring', 1, '2021-05-07 04:53:56', NULL),
(41, 'child', '38', 'Low Temperature Bleacing', 1, '2021-05-07 04:53:56', NULL),
(42, 'parent', '35', 'Peroxide Killing', 1, '2021-05-07 04:53:56', NULL),
(43, 'child', '42', 'Peoxide Killing Enzymes', 1, '2021-05-07 04:53:56', NULL),
(44, 'parent', '35', 'Bio-polishing', 1, '2021-05-07 04:53:56', NULL),
(45, 'child', '44', 'Neutral Bio-Polishing', 1, '2021-05-07 04:53:56', NULL),
(46, 'child', '44', 'Acidic Bio-Polishing', 1, '2021-05-07 04:53:56', NULL),
(47, 'parent', '34', 'Dyeing', 1, '2021-05-07 04:53:56', NULL),
(48, 'parent', '47', 'Soaping', 1, '2021-05-07 04:53:56', NULL),
(49, 'child', '48', 'Enzymatic Washing Off', 1, '2021-05-07 04:53:56', NULL);


INSERT INTO `raw_materials` (`id`, `name`, `coding`, `price`, `shelf_life`, `opening_stock`, `status`, `created_at`, `updated_at`) VALUES
(1, 'RM1', 'RM1', 100.00, 15, 100, 1, '2021-05-06 05:33:40', '2021-05-06 06:53:23'),
(2, 'Rm2', 'RM2', 200.00, 15, 1500, 1, '2021-05-06 06:53:13', '2021-05-06 06:53:13'),
(3, 'Water', 'Water', 0.00, 15, 1000000, 1, '2021-05-06 06:53:41', '2021-05-06 06:53:41'),
(4, 'Rm3', 'Rm3', 300.00, 12, 10000, 1, '2021-05-06 23:13:28', '2021-05-06 23:13:28');


INSERT INTO `packing_sizes` (`id`, `type`, `size`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Can', 30.00, 1, '2021-05-06 06:37:32', '2021-05-06 06:39:02'),
(2, 'Drum', 10.00, 1, '2021-05-06 23:13:44', '2021-05-06 23:13:44'),
(3, 'Drum', 20.00, 1, '2021-05-06 23:13:54', '2021-05-06 23:13:54'),
(4, 'Can', 50.00, 1, '2021-05-06 23:14:01', '2021-05-06 23:14:01');