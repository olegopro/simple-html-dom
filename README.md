### Simple HTML DOM multithreading parser

**Скрипт создания базы**

``` sql
CREATE TABLE `articles` (
`id` int UNSIGNED NOT NULL,
`url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
`h1` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
`content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
`data_parse` timestamp NULL DEFAULT NULL,
`tmp_unique` char(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `articles`
--
ALTER TABLE `articles`
ADD PRIMARY KEY (`id`),
ADD UNIQUE KEY `url` (`url`),
ADD KEY `tmp_unique` (`tmp_unique`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `articles`
--
ALTER TABLE `articles`
MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;
```

composer.json:

```json
{
  "require": {
    "ext-pdo": "*",
    "voku/simple_html_dom": "^4.8",
    "ext-curl": "*",
    "symfony/dotenv": "^6.1"
  }
}
```
