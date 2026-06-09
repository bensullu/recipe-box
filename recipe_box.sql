-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: recipe_box
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `recipe_box`
--

/*!40000 DROP DATABASE IF EXISTS `recipe_box`*/;

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `recipe_box` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `recipe_box`;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `category_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (8,'Baking'),(1,'Breakfast'),(5,'Desserts'),(6,'Drinks'),(3,'Main Courses'),(4,'Salads'),(2,'Soups'),(7,'Vegan');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comments` (
  `comment_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `recipe_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `rating` tinyint(3) unsigned NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`comment_id`),
  KEY `fk_comments_recipe` (`recipe_id`),
  KEY `fk_comments_user` (`user_id`),
  CONSTRAINT `fk_comments_recipe` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`recipe_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comments`
--

LOCK TABLES `comments` WRITE;
/*!40000 ALTER TABLE `comments` DISABLE KEYS */;
INSERT INTO `comments` VALUES (1,5,10,5,'Followed the steps exactly and it was amazing.','2026-05-19 11:36:32'),(2,5,6,4,'Delicious, though I tweaked the seasoning a little.','2026-05-15 21:24:45'),(3,6,6,4,'Loved it! Took a little longer than expected though.','2026-05-25 13:28:40'),(4,6,9,4,'Loved it! Took a little longer than expected though.','2026-05-09 17:04:18'),(5,7,11,5,'Absolutely delicious, my whole family loved it!','2026-05-20 22:16:13'),(6,7,8,3,'Good but a bit too rich for my taste.','2026-05-17 23:38:06'),(7,7,10,5,'Restaurant quality at home. Five stars!','2026-06-01 10:26:04'),(8,7,12,5,'This is now a staple in our kitchen.','2026-05-10 13:51:11'),(9,8,11,4,'Very nice, will definitely make again.','2026-06-04 04:50:59'),(10,8,13,5,'Followed the steps exactly and it was amazing.','2026-04-22 13:43:11'),(11,8,10,5,'So good I made it twice in one week.','2026-06-05 10:58:37'),(12,9,6,3,'Decent recipe, needed extra salt for me.','2026-04-15 01:14:54'),(13,9,8,4,'Very nice, will definitely make again.','2026-05-02 04:03:31'),(14,9,12,4,'Delicious, though I tweaked the seasoning a little.','2026-05-05 15:41:22'),(15,10,13,3,'Decent recipe, needed extra salt for me.','2026-04-27 13:19:42'),(16,10,7,5,'Restaurant quality at home. Five stars!','2026-06-02 01:45:42'),(17,11,12,3,'Tasty enough, I might adjust the timing next time.','2026-05-19 21:38:27'),(18,11,13,4,'Great recipe, easy to follow and satisfying.','2026-04-18 09:49:47'),(19,11,10,5,'Turned out perfect on the first try. A keeper!','2026-06-02 07:13:56'),(20,11,11,5,'Turned out perfect on the first try. A keeper!','2026-06-04 18:51:25'),(21,12,9,5,'Followed the steps exactly and it was amazing.','2026-05-22 23:11:06'),(22,12,10,5,'Absolutely delicious, my whole family loved it!','2026-05-24 00:40:17'),(23,13,8,5,'Incredible flavor, thank you for the recipe!','2026-04-11 02:38:05'),(24,13,13,5,'Followed the steps exactly and it was amazing.','2026-05-21 13:01:20'),(25,13,11,4,'Really tasty, I added a bit more spice next time.','2026-06-01 17:42:57'),(26,14,9,4,'Loved it! Took a little longer than expected though.','2026-05-13 22:56:11'),(27,14,8,3,'Decent recipe, needed extra salt for me.','2026-04-25 07:29:39'),(28,14,7,4,'Really tasty, I added a bit more spice next time.','2026-04-28 05:04:14'),(29,15,7,4,'Solid recipe, the family approved.','2026-04-11 15:25:27'),(30,15,6,4,'Loved it! Took a little longer than expected though.','2026-04-18 20:57:56'),(31,16,10,5,'Absolutely delicious, my whole family loved it!','2026-05-06 17:55:20'),(32,16,13,5,'Followed the steps exactly and it was amazing.','2026-05-06 00:43:18'),(33,16,7,5,'Absolutely delicious, my whole family loved it!','2026-05-12 09:01:50'),(34,17,11,4,'Loved it! Took a little longer than expected though.','2026-05-25 07:33:41'),(35,17,12,4,'Great recipe, easy to follow and satisfying.','2026-04-28 04:07:16'),(36,17,6,4,'Really tasty, I added a bit more spice next time.','2026-04-24 18:18:04'),(37,17,10,5,'Absolutely delicious, my whole family loved it!','2026-05-03 01:50:29'),(38,18,7,5,'Turned out perfect on the first try. A keeper!','2026-05-31 11:16:19'),(39,18,8,5,'Incredible flavor, thank you for the recipe!','2026-04-09 13:59:07'),(40,18,13,4,'Great recipe, easy to follow and satisfying.','2026-05-03 16:37:36'),(42,19,8,4,'Solid recipe, the family approved.','2026-04-24 10:02:52'),(43,19,9,5,'Turned out perfect on the first try. A keeper!','2026-04-22 07:08:59'),(46,21,6,4,'Solid recipe, the family approved.','2026-05-28 18:07:00'),(47,21,7,5,'Absolutely delicious, my whole family loved it!','2026-05-17 07:39:47'),(48,21,9,4,'Loved it! Took a little longer than expected though.','2026-04-21 00:46:02'),(49,22,9,4,'Loved it! Took a little longer than expected though.','2026-04-22 10:42:06'),(50,22,6,5,'Incredible flavor, thank you for the recipe!','2026-04-13 16:50:26'),(51,22,8,4,'Very nice, will definitely make again.','2026-05-17 19:26:03'),(52,23,11,4,'Really tasty, I added a bit more spice next time.','2026-04-25 10:18:21'),(53,23,13,4,'Solid recipe, the family approved.','2026-04-13 22:27:23'),(54,23,8,5,'Restaurant quality at home. Five stars!','2026-04-13 19:11:15'),(55,24,10,5,'This is now a staple in our kitchen.','2026-05-07 13:34:13'),(56,24,6,5,'Restaurant quality at home. Five stars!','2026-04-13 20:32:08'),(57,24,9,4,'Delicious, though I tweaked the seasoning a little.','2026-04-20 03:03:31'),(58,24,13,4,'Great recipe, easy to follow and satisfying.','2026-04-30 01:02:41'),(59,25,7,5,'Restaurant quality at home. Five stars!','2026-04-11 05:23:58'),(60,25,11,3,'Nice, but mine didn\'t look as pretty as the photo.','2026-05-19 07:22:13'),(61,25,13,3,'Good but a bit too rich for my taste.','2026-05-01 22:33:29'),(62,25,12,4,'Very nice, will definitely make again.','2026-05-29 15:08:55'),(64,26,12,5,'Incredible flavor, thank you for the recipe!','2026-05-10 10:41:08'),(65,26,11,5,'Incredible flavor, thank you for the recipe!','2026-05-14 15:06:49'),(67,26,15,4,'4564564','2026-06-06 23:12:31'),(69,26,15,5,'hkghjk','2026-06-09 14:30:06');
/*!40000 ALTER TABLE `comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `favorites`
--

DROP TABLE IF EXISTS `favorites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `favorites` (
  `favorite_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `recipe_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`favorite_id`),
  UNIQUE KEY `unique_recipe_user` (`recipe_id`,`user_id`),
  KEY `fk_favorites_user` (`user_id`),
  CONSTRAINT `fk_favorites_recipe` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`recipe_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_favorites_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `favorites`
--

LOCK TABLES `favorites` WRITE;
/*!40000 ALTER TABLE `favorites` DISABLE KEYS */;
INSERT INTO `favorites` VALUES (3,19,14),(2,20,14),(4,26,15);
/*!40000 ALTER TABLE `favorites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recipes`
--

DROP TABLE IF EXISTS `recipes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recipes` (
  `recipe_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `category_id` int(10) unsigned NOT NULL,
  `prep_time` smallint(5) unsigned NOT NULL,
  `servings` tinyint(3) unsigned NOT NULL,
  `difficulty` enum('Easy','Medium','Hard') NOT NULL DEFAULT 'Easy',
  `ingredients` text NOT NULL,
  `instructions` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`recipe_id`),
  KEY `fk_recipes_category` (`category_id`),
  CONSTRAINT `fk_recipes_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recipes`
--

LOCK TABLES `recipes` WRITE;
/*!40000 ALTER TABLE `recipes` DISABLE KEYS */;
INSERT INTO `recipes` VALUES (5,'Menemen',1,20,2,'Easy','4 eggs\n3 ripe tomatoes\n2 green peppers\nolive oil\nsalt, black pepper, red pepper flakes','Saute the chopped peppers in olive oil.\nAdd the grated tomatoes and cook until soft.\nSeason with salt and spices.\nCrack in the eggs and stir gently until just set.\nServe hot with fresh bread.','recipe_6a2439be5b8d82.93294541.jpg','2026-06-06 15:16:14'),(6,'Sucuklu Yumurta',1,10,2,'Easy','6 slices of sucuk (spicy sausage)\n3 eggs\nbutter\nsalt, pepper','Fry the sucuk slices in a hot pan until they release their oil.\nCrack the eggs over the sucuk.\nCook to your liking, sunny side up or scrambled.\nSeason and serve sizzling in the pan.','recipe_6a2439bf454a41.79040503.jpg','2026-06-06 15:16:15'),(7,'Mercimek Çorbası',2,40,4,'Easy','1.5 cups red lentils\n1 onion\n1 carrot\n1 potato\n2 tbsp flour\n6 cups stock\nbutter, cumin, mint, salt','Saute the chopped onion in butter.\nAdd carrot, potato, lentils and stock.\nSimmer until everything is soft, about 25 minutes.\nBlend until smooth.\nSeason with cumin and salt; serve with lemon and dried mint.','recipe_6a2439c0570167.96668703.jpg','2026-06-06 15:16:16'),(8,'Ezogelin Çorbası',2,45,4,'Easy','1 cup red lentils\n3 tbsp bulgur\n2 tbsp rice\n1 onion\n2 tbsp tomato paste\n6 cups water\nmint, red pepper, salt','Saute the onion with tomato paste.\nAdd lentils, bulgur, rice and water.\nSimmer until grains are tender.\nSeason with dried mint and red pepper.\nDrizzle with melted butter and pepper flakes before serving.','recipe_6a2439c126b101.44524512.jpg','2026-06-06 15:16:17'),(9,'Adana Kebabı',3,40,4,'Medium','700g minced lamb\n1 tsp red pepper flakes\nsalt, cumin\nflat skewers\nlavash bread\ngrilled tomatoes and peppers','Knead the minced lamb with salt and spices until sticky.\nShape onto wide flat skewers.\nGrill over hot charcoal, turning often.\nServe on lavash with grilled tomatoes, peppers and sumac onions.','recipe_6a2439c2477776.32759056.jpg','2026-06-06 15:16:18'),(10,'İskender Kebap',3,50,4,'Medium','500g doner meat\n2 pide breads (cubed)\n2 cups tomato sauce\n200g yogurt\n100g melted butter','Lay cubed pide bread on a plate.\nArrange the sliced doner meat on top.\nPour hot tomato sauce over everything.\nAdd a side of yogurt and pour sizzling melted butter on top.\nServe immediately.','recipe_6a2439c36fa253.47911257.jpg','2026-06-06 15:16:19'),(11,'Karnıyarık',3,60,4,'Medium','6 small eggplants\n300g minced beef\n2 onions\n3 tomatoes\n2 green peppers\nparsley, oil, salt','Fry the eggplants and slit them open.\nCook the mince with onion, tomato and parsley.\nFill each eggplant with the meat mixture.\nTop with tomato and pepper slices.\nBake at 190C for about 25 minutes.','recipe_6a2439c4ab8ab9.18479646.jpg','2026-06-06 15:16:20'),(12,'Mantı',3,90,4,'Hard','3 cups flour\n2 eggs\nwater, salt\n300g minced beef\n1 onion\ngarlic yogurt\nbutter, mint, sumac','Make a firm dough and rest it.\nRoll thin and cut into small squares.\nPlace a little spiced mince in each, pinch into tiny dumplings.\nBoil until they float.\nServe with garlic yogurt and minted butter sauce.','recipe_6a2439c5b6e565.79293164.jpg','2026-06-06 15:16:21'),(13,'Lahmacun',3,50,4,'Medium','thin flatbread dough\n300g minced lamb\n2 tomatoes\n1 onion\n1 pepper\nparsley, paprika, salt','Blend tomatoes, onion, pepper, parsley and mince into a paste.\nRoll the dough very thin.\nSpread a thin layer of the meat mixture.\nBake in a very hot oven until crisp.\nServe with lemon and fresh parsley, rolled up.','recipe_6a2439c6808db4.60748009.jpg','2026-06-06 15:16:22'),(14,'İzmir Köftesi',3,50,4,'Medium','500g minced beef\r\n1 slice bread\r\n1 egg\r\n2 potatoes\r\n3 tomatoes\r\nonion, cumin, parsley, salt','Mix mince with soaked bread, egg, grated onion and spices.\r\nShape into oval koftes.\r\nFry with potato wedges until browned.\r\nArrange in a tray with tomato sauce.\r\nBake until the sauce thickens.','recipe_6a27fc9f56b5d5.34028552.webp','2026-06-06 15:16:23'),(15,'İmam Bayıldı',7,60,4,'Medium','4 eggplants\n3 onions\n4 tomatoes\n4 garlic cloves\nparsley\nolive oil, sugar, salt','Fry the eggplants and slit them lengthwise.\nCook sliced onion, garlic and tomato in olive oil.\nStuff the eggplants generously with the mixture.\nAdd water, a little sugar and olive oil.\nSimmer gently until very soft; serve cold.','recipe_6a243af0ee2e56.06412989.jpg','2026-06-06 15:16:24'),(16,'Zeytinyağlı Yaprak Sarma',7,80,6,'Hard','40 grape leaves\r\n2 cups rice\r\n3 onions\r\npine nuts, currants\r\nolive oil\r\nmint, dill, lemon, salt','Saute onion, rice, pine nuts and currants with spices.\r\nPlace a little filling on each leaf and roll tightly.\r\nLayer the rolls snugly in a pot.\r\nAdd olive oil, lemon and water; weigh down with a plate.\r\nSimmer gently until tender; serve cold.','recipe_6a27fc7ba34419.60982449.jpg','2026-06-06 15:16:24'),(17,'Çoban Salatası',4,15,4,'Easy','4 tomatoes\n2 cucumbers\n1 onion\n2 green peppers\nparsley\nolive oil, lemon, salt','Finely dice the tomatoes, cucumbers, onion and peppers.\nChop the parsley.\nCombine everything in a bowl.\nDress with olive oil, lemon juice and salt.\nToss and serve fresh.','recipe_6a243af664e5b4.29012530.jpg','2026-06-06 15:16:25'),(18,'Gavurdağı Salatası',4,20,4,'Easy','4 tomatoes\n1 onion\nwalnuts\nparsley\npomegranate molasses\nolive oil, sumac, salt','Dice the tomatoes and onion finely.\nAdd crushed walnuts and chopped parsley.\nDress with pomegranate molasses and olive oil.\nSeason with sumac and salt.\nMix well and serve.','recipe_6a243afcab74e7.42118831.jpg','2026-06-06 15:16:26'),(19,'Baklava',5,90,8,'Hard','phyllo dough\n300g walnuts or pistachios\n250g butter\n2 cups sugar\n1.5 cups water\nlemon juice','Layer buttered phyllo sheets in a tray.\nSprinkle ground nuts between the layers.\nCut into diamonds and pour melted butter over.\nBake until golden.\nPour cooled syrup over the hot baklava and let it soak.','recipe_6a243afed6fc11.02481939.png','2026-06-06 15:16:27'),(20,'Künefe',5,40,4,'Medium','250g kadayif (shredded pastry)\n200g unsalted cheese\n150g butter\n2 cups sugar syrup\nground pistachios','Toss the shredded pastry with melted butter.\nPress half into a pan and add the cheese.\nCover with the rest of the pastry.\nCook both sides until golden and crisp.\nSoak with warm syrup and top with pistachios.','recipe_6a243b02e0df41.68981465.jpg','2026-06-06 15:16:27'),(21,'Sütlaç',5,45,6,'Easy','1 litre milk\n0.5 cup rice\n0.75 cup sugar\n2 tbsp rice flour\nvanilla','Boil the rice in a little water until soft.\nAdd milk and sugar and simmer.\nStir in rice flour slurry to thicken.\nPour into bowls.\nOptionally bake the tops until golden; chill before serving.','recipe_6a244039cfb899.34985955.jpg','2026-06-06 15:16:28'),(22,'Lokum',5,60,10,'Medium','4 cups sugar\n1.5 cups cornstarch\n4.5 cups water\nlemon juice\nrosewater\npowdered sugar','Boil sugar and water with lemon juice into a syrup.\nWhisk cornstarch with water and combine.\nCook slowly, stirring, until thick and golden.\nFlavor with rosewater and pour into a tray.\nLet set, cut into cubes and dust with powdered sugar.','recipe_6a244039d08373.85925091.jpg','2026-06-06 15:16:28'),(23,'Ayran',6,5,2,'Easy','2 cups plain yogurt\n1 cup cold water\nsalt\nice\ndried mint (optional)','Whisk the yogurt until smooth.\nGradually add the cold water.\nAdd salt to taste.\nWhisk until frothy.\nServe over ice.','recipe_6a243b1a6b3db2.74368753.jpg','2026-06-06 15:16:30'),(24,'Türk Kahvesi',6,10,2,'Medium','2 tsp finely ground coffee\n2 small cups water\nsugar to taste','Add water and coffee (and sugar) to a cezve.\nStir once and heat slowly on low.\nAs foam rises, do not let it boil over.\nPour the foam into cups, then return to finish heating.\nPour the rest and let the grounds settle before sipping.','recipe_6a243b258b9975.42198477.jpg','2026-06-06 15:16:30'),(25,'Su Böreği',8,90,8,'Hard','10 sheets yufka\n400g white cheese\nparsley\n3 eggs\n1 cup milk\n1 cup oil\nbutter, salt','Boil each yufka sheet briefly and cool in cold water.\nWhisk eggs, milk, oil and butter as the wetting mix.\nLayer the sheets in a tray, brushing each with the mix.\nAdd cheese and parsley in the middle layers.\nBake at 180C until the top is golden and crisp.','recipe_6a243b28071798.08367054.jpg','2026-06-06 15:16:31'),(26,'Pide',8,60,4,'Medium','bread dough\n300g minced meat or cheese\n1 onion\n1 tomato\n1 pepper\nparsley\negg yolk, butter','Roll the dough into long oval boats.\nFill with spiced mince or cheese.\nFold and pinch the edges into a boat shape.\nBrush the edges with egg yolk.\nBake in a hot oven until golden; brush with butter.','recipe_6a244039cec7f0.02339211.jpg','2026-06-06 15:16:32'),(27,'Çılbır',1,10,4,'Medium','3 Eggs\r\n1 Tbsp Vinegar\r\nYogurt\r\n2 Tbsp Butter\r\nChili Flakes','Pour water into a small saucepan, add salt and vinegar to taste, and bring to a boil.\r\nStir the boiling water with a spoon, creating a whirlpool.\r\nNext, crack the eggs one by one and boil them for about 5 minutes.\r\nUsing a spoon, remove the cooked eggs from the water and place them on a serving plate.\r\nAdd whipped yogurt on top.\r\nNext, melt the butter in a pan and add the chili flakes.\r\nPour the prepared pepper sauce over the eggs.','recipe_6a24a330262358.89979621.webp','2026-06-06 22:46:08');
/*!40000 ALTER TABLE `recipes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `login` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `login` (`login`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (6,'ayse_k','$2y$10$aRLfGBLdAK9w9JdD9r69ve5B2QPeYQlA/SmPnY49AeGh15YcPcI6O','ayse@example.com',0,'2026-06-06 15:59:18'),(7,'mehmet27','$2y$10$aRLfGBLdAK9w9JdD9r69ve5B2QPeYQlA/SmPnY49AeGh15YcPcI6O','mehmet@example.com',0,'2026-06-06 15:59:18'),(8,'zeynep','$2y$10$aRLfGBLdAK9w9JdD9r69ve5B2QPeYQlA/SmPnY49AeGh15YcPcI6O','zeynep@example.com',0,'2026-06-06 15:59:18'),(9,'can_demir','$2y$10$aRLfGBLdAK9w9JdD9r69ve5B2QPeYQlA/SmPnY49AeGh15YcPcI6O','can@example.com',0,'2026-06-06 15:59:18'),(10,'elif','$2y$10$aRLfGBLdAK9w9JdD9r69ve5B2QPeYQlA/SmPnY49AeGh15YcPcI6O','elif@example.com',0,'2026-06-06 15:59:18'),(11,'burak','$2y$10$aRLfGBLdAK9w9JdD9r69ve5B2QPeYQlA/SmPnY49AeGh15YcPcI6O','burak@example.com',0,'2026-06-06 15:59:18'),(12,'deniz_y','$2y$10$aRLfGBLdAK9w9JdD9r69ve5B2QPeYQlA/SmPnY49AeGh15YcPcI6O','deniz@example.com',0,'2026-06-06 15:59:18'),(13,'selin','$2y$10$aRLfGBLdAK9w9JdD9r69ve5B2QPeYQlA/SmPnY49AeGh15YcPcI6O','selin@example.com',0,'2026-06-06 15:59:18'),(14,'admin','$2y$10$GHRufVg54QFW9eqaLBvbAeU5ky9644D.mRchcKdmsT/9xaCdH.3GC','admin@recipebox.local',1,'2026-06-06 15:59:35'),(15,'oıo','$2y$10$l2w45NoQ0xUpjLk.OQ4qAecKc9WrpcNQKKNaLxHOuPRHzO8WQGbTe','ogretmen@test.com',0,'2026-06-06 23:12:09');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-09 17:25:38
