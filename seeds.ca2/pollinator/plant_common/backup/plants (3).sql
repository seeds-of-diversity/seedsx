-- phpMyAdmin SQL Dump
-- version 3.5.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Mar 18, 2013 at 03:58 AM
-- Server version: 5.5.24-log
-- PHP Version: 5.3.13

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `plants`
--

-- --------------------------------------------------------

--
-- Table structure for table `location`
--

CREATE TABLE IF NOT EXISTS `location` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Location_Code` varchar(32) NOT NULL,
  `Location_Description` varchar(32) NOT NULL,
  `Active_Province` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=14 ;

--
-- Dumping data for table `location`
--

INSERT INTO `location` (`ID`, `Location_Code`, `Location_Description`, `Active_Province`) VALUES
(1, 'AB', 'Alberta', 0),
(2, 'BC', 'Bristish Columbia', 0),
(3, 'MB', 'Manitoba', 0),
(4, 'NB', 'New Brunswick', 0),
(5, 'NF', 'Newfoundland & Labrador', 0),
(6, 'NT', 'North West Territories', 0),
(7, 'NS', 'Nova Scotia', 0),
(8, 'NU', 'Nunavut', 0),
(9, 'ON', 'Ontartio', 1),
(10, 'PE', 'Prince Edward Island', 0),
(11, 'QC', 'Quebec', 0),
(12, 'SK', 'Saskatchewan', 0),
(13, 'YT', 'Yukon', 0);

-- --------------------------------------------------------

--
-- Table structure for table `main`
--

CREATE TABLE IF NOT EXISTS `main` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Scientific_Name` varchar(128) CHARACTER SET utf8 DEFAULT NULL,
  `Common_Name` varchar(128) CHARACTER SET utf8 DEFAULT NULL,
  `Bee_Resource` varchar(32) CHARACTER SET utf8 DEFAULT NULL,
  `Season` varchar(64) CHARACTER SET utf8 DEFAULT NULL,
  `Plant_Type` varchar(45) CHARACTER SET utf8 DEFAULT NULL,
  `Location` varchar(128) CHARACTER SET utf8 DEFAULT NULL,
  `image_flower` varchar(256) CHARACTER SET utf8 DEFAULT NULL,
  `image_fruit` varchar(256) CHARACTER SET utf8 DEFAULT NULL,
  `image_leaves` varchar(256) CHARACTER SET utf8 DEFAULT NULL,
  `image_habitus` varchar(256) CHARACTER SET utf8 DEFAULT NULL,
  `info_text` text CHARACTER SET utf8,
  `data` varchar(128) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=205 ;

--
-- Dumping data for table `main`
--

INSERT INTO `main` (`ID`, `Scientific_Name`, `Common_Name`, `Bee_Resource`, `Season`, `Plant_Type`, `Location`, `image_flower`, `image_fruit`, `image_leaves`, `image_habitus`, `info_text`, `data`) VALUES
(1, 'Acer negundo', 'Box Elder', 'P', 'eSp', 'W', 'AB,BC,MB,NT,NS,ON,PE,QC,SK,YT', 'images\\Acer negundo_flowers_copyright Flickr.Putneypics.jpg', 'images\\Acer negundo_leaves_copyright Steven J. Baskauf_bioimages.vanderbilt.edu.jpg', NULL, 'images\\Acer negundo_habitus_copyright Steven J. Baskauf_bioimages.vanderbilt.edu.jpg', 'This early spring blooming tree does not produce nectar.  Only the male trees produce pollen, copiously, and are worked extensively by honeybees in spring.', NULL),
(2, 'Acer saccharinum', 'Silver maple', 'P', 'eSp', 'W', 'AB,BC,MB,NT,NS,ON,PE,QC,SK,YT', 'images\\Acer saccharinum_flower_copyright Steven J. Baskauf_bioimages.vanderbilt.edu.jpg', 'images\\Acer saccharinum_fruit_copyright Jason Sturner_(CC by 2.0).jpg', 'images\\Acer saccharinum_habitus_public domain.jpg', 'images\\Acer saccharinum_habitus_public domain.jpg', 'The early spring blooms of this widespread tree of eastern Canada can be an important source of nectar.  It is not known by beekeepers for pollen production. ', NULL),
(3, 'Acer rubrum', 'Red Maple', 'N,P', 'eSp, mSp, lSp, eSu', 'W', 'NB, NF, NS, ON, PE, QC', NULL, NULL, NULL, NULL, 'This early blooming tree of wet areas produces large amounts of nectar.  Trees may literally buzz with honeybees working the tufts of reddish flowers.  It is not known by beekeepers for pollen production.', NULL),
(4, 'Acer spp.', 'Maple', 'P, n', 'eSp, mSp, lSp, eSu', 'W,C', 'AB,BC,MB,NF,NT,NS,ON,PE,QC,SK,YT', ' ', NULL, NULL, NULL, 'Some maples have flowers that are rarely visited by honeybees for pollen, and produce little or no nectar.  ', NULL),
(5, 'Aesculushippocastanum', 'Horse Chestnut', 'N,P,r', 'lSp, eSu', 'W', 'BC, NB, NS, ON, QC', 'images\\Aesculus hippocastanum_flowers_copyright Flickr.blumenbiene_(CC by 2.0).jpg', 'images\\Aesculus hippocastanum_fruit_copyright Flickr.D.H. Wright_(CC by 2.0).jpg', 'images\\Aesculus hippocastanum_leaf_copyright Steven J. Baskauf_bioimages.vanderbilt.edu.jpg', 'images\\Aesculus hippocastanum_habitus_copyright Flickr.Flowrsabc_(CC by-SA 2.0).jpg', 'This well-known ornamental tree produces great amounts of nectar that is eagerly collected by honeybees, as is the pollen.  Both nectar and pollen have been reported to be toxic to honeybees when other forage is not available', NULL),
(6, 'Aesculus spp.', 'Buckeye', 'N,P,r', 'mSu', 'C', 'BC, NB, ON, QC', NULL, NULL, NULL, NULL, 'There are a number of species, hybrids and cultivars used as ornamentals.  Some such as the Ohio buckeye, A. glabra, are valued for nectar and honey production.', NULL),
(7, 'Ajugareptans', 'Common bugle', 'N,P', 'lSp,eSu,mSu', 'R', 'BC, NF, NS, ON, QC', ' ', NULL, NULL, NULL, 'A weedy plant that grows along roadsides, vacant land, and sometimes in uncultivated fields. A good source of nectar.', NULL),
(8, 'Allium cernuum', 'Nodding Onion', 'n,p', 'mSu', 'W', 'AB, BC, ON, SK', NULL, NULL, NULL, NULL, NULL, NULL),
(9, 'Alnus spp.', 'Alder', 'P', 'eSp', 'W', 'AB,BC,MB,NF,NT,NS,ON,PE,QC,SK,YT', ' ', NULL, NULL, NULL, 'These early blooming shrubs and treelets of wet areas, along streams and ditches, around ponds and lakes produce copious amounts of pollen in spring.  Although the pollen is not protein rich, it is eagerly collected by honeybees and considered valuable by beekeepers.', NULL),
(10, 'Althea rosea', 'Hollyhock', 'P', 'mSu', 'C', 'AB, BC, MB, NB, NF, NS, ON, PE, QC, SK', NULL, NULL, NULL, NULL, 'Commonly grown garden plant. Produces pollen copiously, can attract many honeybees, but may also be ignored by them. Well used by bumblebees. Flora nectar is gathered by honeybees, as may be honeydew from sap-sucking insects if numerous on the plants. ', NULL),
(11, 'Alyssum saxatilis', 'Golden cress, Basket of Gold', 'N,P', 'eSu', 'C', 'ON', NULL, NULL, NULL, NULL, 'A plant of rock gardens. Of minor or little importance to beekeeping.', NULL),
(12, 'Ambrosia trifida', 'Giant Ragweed', 'P', 'mSu', 'R', 'AB, MB, NB, NS, ON, PE, QC, SK', 'images\\Ambrosia trifida_flowers_copyright Le.Loup.Gris_(CC by-SA 3.0).jpg', NULL, 'images\\Ambrosia trifida_leaves_copyright Jeff McMillian_plants.usda.gov.jpg', 'images\\Ambrosia trifida_habitus_copyright Le.Loup.Gris_(CC by-SA 3.0).jpg', 'This aggressive weed of cultivated fields, especially at the edges is a source of pollen sometimes collected by honeybees. ', NULL),
(13, 'Amelanchieralnifolia', 'Serviceberry, Saskatoon', 'N,P', 'lSp, eSu', 'W', 'AB,BC,MB,NT,NS,ON,PE,QC,SK,YT', NULL, NULL, NULL, NULL, 'This small tree of woodland margins, thickets, stream banks and regenerating old-fieldsproduces masses of white flowers early in summer.  The flowers are well visited by honeybees for both nectar and pollen.  The value of this plant to beekeeping is not well recognized.', NULL),
(14, 'Amorphafruticosa', 'False indigo', 'N,P', 'eSu', 'W', 'MB, NB, ON, QC', NULL, NULL, NULL, NULL, 'This shrub can form quite dense thickets along stream and riverbanks and is used by honeybees for nectar and pollen after fruit trees have bloomed and before clovers have started.', NULL),
(15, 'Anenome patens', 'Prairie crocus', 'P', 'eSp', 'W', 'AB, BC, MB, NT, ON, SK, YT', 'images\\Anemone patens_flowers_copyright user.wiki.Jerzy Strzelecki_(CC by-SA 3.0).jpg', NULL, 'images\\Anemone patens_leaves_copyright user.wiki.SriMesh_(CC by-SA 3.0).jpg', 'images\\Anemone patens_habitus_copyright user.wiki.Unomano_(CC by-SA 3.0).jpg', 'Grows in open grassy areas.  A good source of pollen early in the year.', NULL),
(16, 'Angelica atropurpurea', 'Angelica', 'n,p', 'mSu', 'W', 'NB, NF, NS, ON, PE, QC', NULL, NULL, NULL, NULL, 'Grows widely in bottom lands, swamps and rich woodlands.  It is not noted as important to honeybees or beekeeping, but may be important locally.', NULL),
(17, 'Anthyllisvulneraria', 'Kidney vetch', 'N,P', 'mSu', 'East', 'MB, NB, NF, ON, QC', NULL, NULL, NULL, NULL, 'Grows locally in fields and wastelands.  It is used by honeybees locally as a source of pollen and nectar, but not regarded as important to beekeeping', NULL),
(18, 'Antirrhinum majus', 'Snapdragon', 'N,P', 'mSu', 'C', 'AB, BC, MB, NB, NF, NS, ON, PE, QC, SK', NULL, NULL, NULL, NULL, 'Commonly grown garden plant, produces abundant nectar and pollen.  It is well used by ', NULL),
(19, 'Apocynum cannabinum', 'Dogbane, Indian Hemp', 'N,P', 'mSu, lSu', 'W,R', 'AB, BC, MB, NB, NF, NS, NT, ON, PE, QC, SK, YT', NULL, NULL, NULL, NULL, NULL, NULL),
(20, 'Aralia spp.', 'Sarsaparilla', 'N,P', 'mSu, lSu', 'W', 'AB,BC,MB,NF,NT,NS,ON,PE,QC,SK,YT', NULL, NULL, NULL, NULL, 'Wild sarsaparillas are woodland plants, not considered important for honeybees and beekeeing, but well used by some species of bumblebees', NULL),
(21, 'Arctiumlappa and A. minus', 'Burdock', 'N, P', 'mSu, lSu, eF, mF', 'R', 'AB, BC, MB, NB, ON, QC, SK', NULL, NULL, NULL, NULL, 'Familiar, large-leaved weeds with purplish flowering heads.  Not noted as important for honeybees, but well used by bumblebees for both nectar and pollen over its long blooming period from mid-summer to fall.', NULL),
(22, 'Asclepiassyriaca', 'Milkweed', 'N', 'mSu', 'R,W', 'AB, BC, MB, NB, NF, NS, ON, PE, QC, SK', 'images\\Asclepias syriaca_flowers_copyright Steven J. Baskauf_bioimages.vanderbilt.edu.jpg', 'images\\Asclepias syriaca_fruit_credit Cara Dawson.jpg', 'images\\Asclepias syriaca_leaves_credit Cara Dawson.jpg', 'images\\Asclepias syriaca_habitus_copyright Steven J. Baskauf_bioimages.vanderbilt.edu.jpg', 'Field milkweed is an excellent source of nectar.  It grows abundantly, sometimes in extensive clones along roadsides and both cultivated and uncultivated lands.  Its pollen is produced in packets (pollinia) and is not collected by honeybees, except accidentally as the pollinia attach to the bees’ legs. ', NULL),
(23, 'Asparagus officinale', 'Asparagus', 'N, P', 'mSu', 'C,w,r', 'BC, ON, QC, NS, NB, PE', NULL, NULL, NULL, NULL, 'he well-known asparagus is grown commercially and escaped plants are established along fence lines, roadsides, and field margins.  Honeybees forage for nectar from the flowers of both male and female plants, and collect pollen from the flowers of male plants.  Not noted as important to honeybees or to beekeeping.  ', NULL),
(24, 'Astragaluscicer', 'Milkvetch', 'N,P', 'mSu', 'C', 'AB,MB', NULL, NULL, NULL, NULL, 'This plant is sometimes grown as a cover crop and for soil improvement.  It is an attractive nectar provider to honeybees.', NULL),
(25, 'Azalea (Rhododendron)', 'Azalea & Rhododendron', 'N,P', 'eSp, mSp, lSp, eSu, mSu, lSu,eF,mF,lF', 'C', 'AB, BC, MB, NB, NF, NS, NT, NU, ON, PE, QC, SK, YT', NULL, NULL, NULL, NULL, 'There are several species and many cultivars of these shrubs used horticulturally.  Those, and the wild species are not known as important for honeybees, but some are well used by bumblebees as sources of nectar and pollen. ', NULL),
(26, 'Baptisiaaustralis', 'Blue false indigo', 'N,P', 'eSu, mSu, lSu', 'C by beekeepers?', 'ON', NULL, NULL, NULL, NULL, 'Naturally, this plant grows in rich woods and thickets with plenty of light.  It is also grown ornamentally. ', NULL),
(27, 'Baptisiatinctoria', 'Yellow indigo', 'N,P', 'mSu', 'W', 'ON', NULL, NULL, NULL, NULL, 'Grwos in open areas and can be a good nectar source for honeybees.', NULL),
(28, 'Berberis spp. ', 'Barberry', 'N,P', 'eSu,mSu', 'W', 'BC, MB, NB, NS, ON, PE, QC, SK', NULL, NULL, NULL, NULL, 'These bushes, when abundant, are well visited by honeybees for nectar and pollen. ', NULL),
(29, 'Betula spp.', 'Birch ', 'P', 'eSp, mSp, lSp', 'W', 'AB,BC,MB,NF,NT,NS,ON,PE,QC,SK,YT', NULL, NULL, NULL, NULL, 'These early blooming woodland trees produce copious amounts of pollen in early spring.  The pollen is probably not protein rich, but can be eagerly collected by honeybees when no other pollen sources are available.  ', NULL),
(30, 'Boragoofficinalis', 'Borage', 'N,P', 'mSu, lSu, eF, mF', 'C, r', 'AB, BC, MB, NB, NF, NS, ON, PE, QC, SK', NULL, NULL, NULL, NULL, 'As a crop and garden plant, it can be an important local source of high quality nectar.  The long blooming season can be especially valuable. Pollen grains are small and the plant may be ignored as a pollen source', NULL),
(31, 'Brassica spp.', 'Canola, Mustards', 'N, P', 'eSu, mSu, lSu', 'C/R', 'AB, BC, MB, NB, NF, NS, ON, PE, QC, SK', NULL, NULL, NULL, NULL, 'Canola and mustard are grown extensively as crop plants for their oily seeds.  Their yellow flowers produce nectar and pollen copiously.  Honey derived from these plants granulates readily.  The pollen is highly nutritious for honeybees.  Other vegetable brassicas (cabbage, broccoli, cauliflower, etc.) are not valuable as nectar or pollen plants because they are harvested before flowering occurs.', NULL),
(32, 'Calthapalustris', 'Marsh marigold', NULL, NULL, NULL, NULL, 'images\\Caltha palustris_flowers_copyright J.Dennett.jpg', NULL, 'images\\Caltha palustris_leaves_copyright Walter Siegmund_(CC by-SA 3.0).jpg', 'images\\Caltha palustris_habitus_public domain.jpg', 'Flowers in wet and flooded woodland areas early in spring.  Its main importance for beekeeping is as a spring pollen provider. ', NULL),
(33, 'Campanula rapunculoides', 'Bellflower', 'N,P', 'mSu, lSu, eF, mF', 'R', 'AB, BC, MB, NB, NF, NS, ON, PE, QC, SK', NULL, NULL, NULL, NULL, NULL, NULL),
(34, 'Caraganaarborescens', 'Caragana', 'N, P', 'eSu', 'C', 'AB, BC, MB, NB, NT, ON, QC, SK, YT', 'images\\Caragana arborescens_flowers_copyright Andrew Butko_(CC by-SA 3.0).jpg', NULL, 'images\\Caltha palustris_leaves_copyright Walter Siegmund_(CC by-SA 3.0).jpg', 'images\\Caltha palustris_habitus_public domain.jpg', 'These smallish trees of open areas produce nectar and pollen in abundance.  It is a major source of nectar and important in the early summer honey flow is some places. The flowers are also well visited by bumblebees for both pollen and nectar. ', NULL),
(35, 'Carumcarvi', 'Caraway', 'N,P', 'mSu', 'R', 'AB, BC, MB, NB, NF, NS, ON, PE, QC, SK', NULL, NULL, NULL, NULL, 'Caraway is grown for its seeds.  Its flowers produce nectar and pollen that is used by honeybees, but only very locally.', NULL),
(36, 'Carthamnustinctorius', 'Safflower', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Where this plant is grown as a crop,  it can be a valuable source of nectar.  Honeybees tend to work the flowering heads in the morning.', NULL),
(37, 'Carya', 'Hickory', 'P', 'eSp', 'W', 'ON, QC', NULL, NULL, NULL, NULL, 'These early blooming woodland trees  (hickory) produce large amounts of pollen in early spring.  The pollen is probably not protein rich, but can be eagerly collected by honeybees when no other pollen sources are available.  ', NULL),
(38, 'Castaneadentata', 'Chestnut', 'P,r', 'eSu', 'W(v. rare)', 'ON', NULL, NULL, NULL, NULL, 'These rare, early blooming woodland trees (chestnut) produce  large amounts of pollen in early spring.  The pollen is probably not protein rich, but can be eagerly collected by honeybees when no other pollen sources are available.  ', NULL),
(39, 'Catalpa speciosa', 'Catalpa', 'N,P', 'mSu', 'C', 'ON, QC, BC', NULL, NULL, NULL, NULL, 'This widely grown ornamental tree has flowers suited to the visits of bumblebees. Although the flowers produce nectar and pollen, it is not known as an important plant for beekeeping.', NULL),
(40, 'Centaurea spp.  ', 'Knapweeds, Cornflowers', 'N,p', 'lSu, eF', 'R', 'AB, BC, MB, NB, NF, NS, ON, PE, QC, SK', NULL, NULL, NULL, NULL, 'Although valuable as honey plants, these mostly weedy species are only minor producers of pollen.  Honeybees often forage from the flowers throughout the day. They grow prolifically on roadsides, rights of way, and uncultivated open areas.  The common cornflower, a popular garden plant, attracts many flower visiting insects.', NULL),
(41, 'Cephalanthusoccidentalis', 'Button Bush', 'N,P', 'mSu, lSu', 'W', 'ON. PE, QC, NS, NB', NULL, NULL, NULL, NULL, 'Native to eastern Canada, this shrub grows in wet, open areas. I can be a valued plant for beekeeping, notably for its nectar, because when it blooms few other flowers are available.  ', NULL),
(42, 'Chamaenerion (now Chamerion) angustifolium', 'Fireweed', 'N', 'mSu, lSu, eF, mF', 'W,R', 'AB,BC,MB,NF,NT,NS,NU, ON,PE,QC,SK,YT', NULL, NULL, NULL, NULL, 'Grows well in open areas, especially after forest fires.  It can form huge stands with flowers that secrete large amounts of nectar.  Honeybees tend not to collect its pollen with its grains held together loosely with strands of viscin. ', NULL),
(43, 'Chamerionangustifolium', 'Fireweed', 'N', 'mSu, lSu, eF, mF', 'W,R', 'AB,BC,MB,NF,NT,NS,NU, ON,PE,QC,SK,YT', NULL, NULL, NULL, NULL, 'Grows well in open areas, especially after forest fires.  It can form huge stands with flowers that secrete large amounts of nectar.  Honeybees tend not to collect its pollen with its grains held together loosely with strands of viscin. ', NULL),
(44, 'Chierathus spp. ', 'Wall -flower', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'These plants are popular garden flowers are worked by honeybees and many other flower visitors for their abundant nectar and pollen.', NULL),
(45, 'Cichoriumintybus', 'Chicory', 'N,P', 'mSu, lSu, eF', 'R', 'AB, BC, MB, NB, NF, NS, ON, PE, QC, SK', 'images\\Cichorium intybus_flower_credit Cara Dawson.jpg', NULL, 'images\\Cichorium intybus_leaves_copyright Steven J. Baskauf_bioimages.vanderbilt.edu.jpg', 'images\\Cichorium intybus_habitus_credit Cara Dawson.jpg', 'A weedy plant that grows along roadsides, vacant land, and sometimes in uncultivated fields. Flowers tend to close in the afternoon. A good source of nectar and pollen.', NULL),
(46, 'Cirsiumarvensis', 'Canada Thistle', 'N,p', 'mSu,lSu,eF,mF', 'R', 'AB, BC, MB, NB, NF, NS, NT, ON, PE, QC, SK, YT', NULL, NULL, NULL, NULL, 'A weedy plant that grows along roadsides, vacant land, pastures and in uncultivated fields. A good source of nectar and pollen.', NULL),
(47, 'Cirsium spp.', 'Thistles', 'N,P', 'mSu, lSu, eF', 'W,R', 'AB,BC,MB,NF,NT,NS,ON,PE,QC,SK,YT', NULL, NULL, NULL, NULL, 'Generally thistles are good sources of nectar and pollen.', NULL),
(48, 'Cladrastis kentukea', 'Yellow Wood', 'N, P', 'eSu,mSu', 'C', 'ON', NULL, NULL, NULL, NULL, NULL, NULL),
(49, 'Claytonia virginica', 'Spring Beauty', 'N,P', 'eSp', 'W', 'ON, QC', NULL, NULL, NULL, NULL, 'Very early blooming plant of woodlands. Can be useful to honeybees early in spring', NULL),
(50, 'Clematis virginiana', 'Clematis', 'P', 'lSu, eF', 'W,R', 'MB, NB, NS, ON, PE, QC', NULL, NULL, NULL, NULL, 'Climbing plant of forest margins, thickets, and fence lines.  The flowers seem not to produce nectar.  The plant is dioecious (separate sexes) and honeybees collect pollen from male plants.  Ornamental Clematis species are used by honeybees for pollen.  The flowers do not have petals.  The floral array is made up of the coloured sepals. ', NULL),
(51, 'Cleome serrulata', 'Bee plant', 'N,P', 'mSu, lSu, eF, mF', 'W,R', 'AB, BC, MB, NT, ON, QC, SK', NULL, NULL, NULL, NULL, NULL, NULL),
(52, 'Cornusstolonifera', 'Red osier dogwood', 'N,P', 'mSu', 'W', 'AB,BC,MB,NF,NT,NS,NU, ON,PE,QC,SK,YT', NULL, NULL, NULL, NULL, 'The red osier dogwood is a prolific producer of nectar and pollen used by many insects and well used by honeybees, especially as a early summer nectar source.', NULL),
(53, 'Cornus sericea', 'Red osier dogwood', 'N,P', 'mSu', 'W', 'AB,BC,MB,NF,NT,NS,NU, ON,PE,QC,SK,YT', NULL, NULL, NULL, NULL, NULL, NULL),
(54, 'Coronillavaria', 'Crown vetch', 'P', 'mSu, lSu, eF', 'R,C', 'AB, BC, MB, NB, NF, NS, ON, PE, QC, SK', NULL, NULL, NULL, NULL, 'Grows on waste land, roadsides, and other uncultivated rough habitats.  Noted as poor as a nectar plant, but useful to beekeeping for its pollen.', NULL),
(55, 'Corylus', 'Hazelnut', 'P', 'eSp, mSp', 'W,C', 'AB, BC, MB, NB, NF, NS, ON, PE, QC, SK', NULL, NULL, NULL, NULL, 'These early blooming trees of woodland margins and thickets produce large amounts of pollen in early spring.  The pollen is probably not protein rich, but can be eagerly collected by honeybees when no other pollen sources are available.  ', NULL),
(56, 'Cosmos bipinnatus', 'Cosmos', 'N,P', 'mSu, lSu, eF, mF', 'C', 'BC, ON, QC', NULL, NULL, NULL, NULL, 'This garden plant can be an important source of nectar, especially late in the summer and early fall.  It is not known for producing much pollen. ', NULL),
(57, 'Crataegus spp.', 'Hawthorn', 'N,P', 'lSp, eSu', 'W', 'AB, BC, MB, NB, NF, NS, ON, PE, QC, SK', NULL, NULL, NULL, NULL, 'These small trees and bushes of woodland margins, thickets, stream banks and regenerating old-fields produce masses of white flowers early in summer.  The flowers are well visited by honeybees for both nectar and pollen.  The value of this plant to beekeeping is not well recognized.', NULL),
(58, 'Crocus', 'Crocus', 'P', 'eSp', 'C', 'AB, BC, MB, NB, NF, NS, ON, PE, QC, SK', NULL, NULL, NULL, NULL, 'Mostly a garden plant, but can become naturalized in urban and suburban places.  Crocus can be important as an early season source of pollen for honeybees.', NULL),
(59, 'Cucumis sativa', 'Cucumber', 'N,p', 'mSu, lSu, eF, mF', 'C', 'BC,ON, QC, NB, PE, NS', NULL, NULL, NULL, NULL, 'Field cucumbers produce nectar in abundance and it is sometimes gathered by honeybees to produce “floral source” honey.  The pollen, produced only on male flowers, is spiny and sometimes ignored or groomed off by foraging honeybees.', NULL),
(60, 'Cucumismelo', 'Melon', 'N,P', 'mSu', 'C', 'ON', NULL, NULL, NULL, NULL, 'Field grown melons produce nectar in abundance and it is well gathered by honeybees.  The pollen, produced only on male flowers, is spiny and sometimes ignored or groomed off by foraging honeybees.', NULL),
(61, 'Cucurbita spp.', 'Pumpkin', 'N,p', 'mSu', 'C', 'ON,QC', NULL, NULL, NULL, NULL, NULL, NULL),
(62, 'Cyanoglossumofficinale', 'Hounds Tongue', 'N,P', 'eSu, mSu', 'R', 'AB, BC, MB, NB, NS, ON, QC, SK', NULL, NULL, NULL, NULL, 'Hound’s tongue is poorly known as a nectar plant for honeybees, but this widely dispersed weed is probably quite important.', NULL),
(63, 'Cytisusscoparius', 'Scotch broom', 'N,P', 'eSu', 'R', 'BC, NS, PE', NULL, NULL, NULL, NULL, 'The brooms do not produce nectar, but some such as Scotch broom are important sources of pollen early in the year where the plant occurs.  It grows in open areas and considerd to be an invasive', NULL),
(64, 'Daucuscarota', 'Carrot/Queen Anne’s Lace', 'N,P', 'mSu, lSu, eF, mF', 'R', 'AB, BC, MB, NB, NF, NS, NT, NU, ON, PE, QC, SK, YT', NULL, NULL, NULL, NULL, 'A weedy plant that grows in abundance along roadsides, vacant land, and sometimes in uncultivated fields. A good source of nectar and pollen.', NULL),
(65, 'Diervillalonicera', 'Bush Honeysuckle', 'N,P', 'mSu', 'W', 'MB, NB, NF, NS, ON, PE, QC, SK', NULL, NULL, NULL, NULL, 'This shrub can be a valuable early source of nectar for honeybees.  The floral tubes are too long to allow then to extract all the nectar.  It is well used ', NULL),
(66, 'Digitalis purpurea', 'Foxglove', 'N,P', 'mSu, lSu, eF', 'C', 'AB, BC, MB, NB, NF, NS, ON, PE, QC, SK', NULL, NULL, NULL, NULL, 'This garden plant can be an important local source of nectar for honeybees.  Each flowering can produce enough nectar to make a load for a honeybee. It produces pollen, but the pollen is reputedly poisonous to honeybees .  The flowers are well visited and worked by bumblebees.', NULL),
(67, 'Diplotaxistenuifolia', 'Wall rocket', 'N, P', 'mSu, lSu, eF, mF', 'R', 'BC, NB, NS, ON, QC', NULL, NULL, NULL, NULL, 'Wall rocket is an adventive weed in the mustard family that grows in stony and waste places.  It produces nectar and pollen that is sometimes taken by honeybees. ', NULL),
(68, 'Dipsacussylvestris', 'Teasel', 'N,P', 'mSu, lSu', 'R', 'BC, ON, QC', NULL, 'images\\Echinocystis lobata_flowers,leaves_public domain.jpg', 'images\\Echinocystis lobata_fruit_public domain.jpg', 'images\\Echinocystis lobata_habitus_public domain.jpg', 'This highly distinctive plant now grows mostly along roadsides and other uncultivated lands.  It produces large amounts of nectar which honeybees seek out eagerly. It is also a good source of pollen.  It is regarded by some as one of the very best bee plants in existence.', NULL),
(69, 'Echinocystuslobata', 'Wild Cucumber', 'n,P', 'mSu, lSu', 'W,R', 'AB, BC, MB, NB, NS, ON, PE, QC, SK', NULL, NULL, NULL, NULL, 'The fast-growing, soft, climbing vines produce clusters of white flowers, most of which are male and produce pollen that may be taken by honeybees at times.  ', NULL),
(70, 'Echiumvulgare', 'Blueweed/Vipers Bugloss', 'N,P', 'mSu, lSu, eF, mF', 'R', 'AB, BC, MB, NB, NF, NS, ON, PE, QC, SK', NULL, NULL, NULL, NULL, 'Although this plant is a notorious weed and grows prolifically along roadsides, rights of way, and unkempt land, it is a highly important and productive nectar plant.  It also produces a great amount of pollen that is well foraged by honeybees. ', NULL),
(71, 'Elaeagnushortensis', 'Russian Olive', 'N,p', 'eSu', 'C', 'AB, BC, MB, NB, NS, ON, QC, SK', NULL, NULL, NULL, NULL, 'The small, yellowish flowers produce large amounts of nectar and pollen.  It is a valuable plant for beekeepers.  It is localized as a landscape plant in gardens, roadsides.  It is becoming more common as an invasive of uncultivated lands.  ', NULL),
(72, 'END OF LIST', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(73, 'Eupatorium perfoliatum ', 'Boneset', 'N,P', 'mSu, lSu, eF, mF', 'W', 'MB, NB, NS, ON, PE, QC', 'images\\Euphorbia esula_flowers_credit Cara Dawson.JPG', NULL, 'images\\Euphorbia esula_leaves_credit Cara Dawson.JPG', 'images\\Euphorbia esula_habitus_credit Cara Dawson.JPG', 'A plant of open, moist, areas along roadsides, ditches, forest margins and thickets. It is an important nectar source for beekeeping late in summer and early fall.', NULL),
(74, 'Euphorbia esula', 'Leafy spurge', 'N,P', 'mSu, lSu, eF', 'R', 'AB, BC, MB, NB, NS, ON, PE, QC, SK, YT', NULL, NULL, NULL, NULL, 'Leafy spurge is a well-known weed that  produces large amounts of extrafloral nectar that is fed on by a wide variety of pollinating and other insects. The nectar can become very sticky by evaporation.  Honeybees are not known to use this source of nectar.  Some spurges are used by honeybees to make characteristically tasting honey.', NULL),
(75, 'Fagopyrumesculentum', 'Buckwheat', 'N,p', 'mSu', 'C', 'AB, BC, MB, NB, NF, NS, ON, PE, QC, SK', NULL, NULL, NULL, NULL, 'This crop plant is highly valued by beekeepers for the dark, characteristic honey that originates from its flowers.  The flowers are not prolific nectar producers, and yields may be variable from time to time and place to place.   Nectar flow is characteristically in the morning.  The value of buckwheat for pollen is low.  The flowers produce rather little, and it may by poisonous to honeybees. ', NULL),
(76, 'Fagusgrandifolia', 'Beech', 'P', 'eSp, mSp, lSp', 'W', 'NB, NS, ON, PE, QC', 'images\\Foeniculum vulgare_flowers_copyright wiki.user.Philmarin_(CC by-SA 3.0).JPG', 'images\\Foeniculum vulgare_seeds_copyright wiki.user.Philmarin_(CC by-SA 3.0).JPG', 'images\\Foeniculum vulgare_habitus_public domain.jpg', NULL, 'These spring blooming and handsome woodland trees produce copious amounts of pollen, but not every year.  The pollen is probably not protein rich, but can be eagerly collected by honeybees when no other pollen sources are available.  ', NULL),
(77, 'Foeniculumvulgare', 'Fennel', 'N,p', 'mSu', 'W,R', 'AB, BC, ON, QC', NULL, NULL, NULL, NULL, 'A weedy plant that grows along roadsides, vacant land, and sometimes in uncultivated fields. A good source of nectar and pollen. It is also grown for seeds and as a herb.', NULL),
(78, 'Fragariavirginiana', 'Strawberry', 'N.P', 'mSu', 'W,C', 'AB,BC,MB,NF,NT,NS,NU, ON,PE,QC,SK,YT', NULL, NULL, NULL, NULL, 'Wild strawberry blossoms are used by honeybees as sources of nectar and pollen.', NULL),
(79, 'Gaylussaciabaccata', 'Black Huckleberry', 'n,p', 'eSu', 'W', 'NB, NF, NS, ON, PE, QC', NULL, NULL, NULL, NULL, 'Huckleberries should be regarded as important nectar plants for honeybees in localized situations, but are not as well known for their value in beekeeping as other members of the heath family (Ericaceae).', NULL),
(80, 'Gentianellacrinata', 'Fringed gentian', 'n,p', 'lSu, eF, mF', 'W', 'MB, ON, QC', NULL, NULL, NULL, NULL, 'The fringed gentian grows in open moist areas and may be a minor source of nectar and pollen for honeybees late in summer.', NULL),
(81, 'Geraniumbicknelli', 'Bicknell''s Cranesbill, Northern Cranesbill', 'N,P', 'mSu', 'W', 'AB,BC,MB,NF,NT,NS,ON,PE,QC,SK,YT', NULL, NULL, NULL, NULL, 'The wild and naturalized geraniums produce nectar and pollen, but their values to honeybees are not well recorded.', NULL),
(82, 'Gleditsiatriacanthos', 'Honey Locust', 'N,P', 'eSu, mSu', 'W,C', 'ON', NULL, NULL, NULL, NULL, 'Despite its name and production of larger amounts of nectar, the bloom period tends to be short.  Honeybees forage from flowers on the trees and those that have fallen. It is native to southern Ontario, but thorn-less cultivars are widely planted along city streets and on roadsides.', NULL),
(83, 'Glycine max', 'Soya bean', 'N', 'mSu', 'C', 'ON,QC', NULL, NULL, NULL, NULL, 'This crop has been reported to provide excellent honey crops in some places.  It is probably that different cultivars differ in their nectar production.  The flowers are mostly or fully self-fertilizing and produce little pollen.', NULL),
(84, 'Ribes uva-crispa', 'Gooseberry', 'N,P', 'eSu,mSu', 'W,C', 'AB,BC,MB,NF,NT,NS,NU, ON,PE,QC,SK,YT', NULL, NULL, NULL, NULL, NULL, NULL),
(85, 'Hamamelisvirginiana', 'Witchhazel', 'n,p', 'mF, lF', 'W', 'NB, NS, ON, PE, QC', NULL, NULL, NULL, NULL, 'The flowers of this shrub are attractive to honeybees for both nectar and pollen. It grows in dry to moist woodlands and thickets in parts of eastern Canada.', NULL),
(86, 'Hedysarumboreale', 'Northern hedysarum', 'N,P', 'eSu, mSu, lSu', 'W', 'AB,BC,MB,NF,NT,NS,NU, ON,PE,QC,SK,YT', 'images\\Helianthus annuus_flower_public domain.jpg', 'images\\Helianthus annuus_fruit_copyright David Wilmot_(CC by-SA 2.0).jpg', NULL, 'images\\Helianthus annuus_habitus_copyright H Zell_(CC by-SA 3.0).JPG', NULL, NULL),
(87, 'Helianthus annuus', 'Sunflower', 'N,P', 'mSu, lSu, eF', 'c,W', 'AB, BC, MB, NB, NF, NS, ON, PE, QC, SK', NULL, NULL, NULL, NULL, 'This crop yields much nectar and is an excellent honey plant.  Nectar is secreted mostly by the florets making up the huge heads, but the plants also have extra-floral nectaries that attract honeybees.  Sometimes honeybees collect the gum produced by the involucre of the heads, probably for propolis production.  Some wild sunflowers may grow prolifically in uncultivated open lands where they are well used by honeybees as sources of nectar.  Sunflower pollen is large and spiny and usually groomed from the bodies of nectar foraging honeybees. ', NULL),
(88, 'Hibiscus trionum', 'Flower-of-an-hour', 'N,P', 'mSu, lSu, eF', 'R', 'MB, NB, NS, ON, PE, QC', 'images\\Hypericum perforatum_flowers_copyright H. Zell_(CC by-SA 3.0).JPG', NULL, 'images\\Hypericum perforatum_leaves_public domain.jpg', 'images\\Hypericum perforatum_habitus_copyright Sten Porse_(CC by-SA 3.0).JPG', NULL, NULL),
(89, 'Hydrophyllumvirginianum', 'Waterleaf', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Grows in damp places and can be a valuable source of nectar for honeybees. ', NULL),
(90, 'Hypericumperforatum', 'St. John’s wort', 'N,P', 'mSu', 'R', 'BC, MB, NB, NF, NS, ON, PE, QC', NULL, NULL, NULL, NULL, 'A common weed sometimes visited for pollen and nectar by honeybees. ', NULL),
(91, 'Impatiens capensis', 'Jewel Weed', 'N, P Mostly Bumblebees', 'mSu, lSu, eF', 'W,R', 'AB, BC, MB, NB, NF, NS, NT, ON, PE, QC, SK, YT', NULL, NULL, NULL, NULL, 'This plant, associated with wet areas in forest and shady places.  It produces large amounts of nectar, but it is hidden in the recurved spur at the flower’s base.  It is usually not well used by honeybees for its nectar or pollen, but is well used by bumblebees and humming birds.', NULL),
(92, 'Ipomoea purpurea', 'Morning glory', 'N,p', 'lSu-F', 'R', 'ON, QC', NULL, NULL, NULL, NULL, 'A garden plant sometimes used by honeybees as a source of nectar', NULL),
(93, 'Juglansniger', 'Walnut', 'P', 'eSp', 'C,W', 'MB, ON, QC', NULL, NULL, NULL, NULL, 'This spring blooming and handsome woodland tree is grown widely in urban settings. It produces copious amounts of pollen.  The pollen is probably not protein rich, but can be eagerly collected by honeybees other pollen sources are scarce.  ', NULL),
(94, 'Kalmia angustifolia', 'Sheep laurel', 'N*,P', 'mSu', 'W', 'NB, NL, NS, ON, PE, QC ', NULL, NULL, NULL, NULL, 'Sheep laurel is not well used by honeybees for either its nectar or pollen. It can be an important source of nectar  in special locations, such as heathlands in the Maritime pronvinces.  Its nectar is poisonous.', NULL),
(95, 'Lathyrustuberosus', 'Tuberous vetchling', 'N,P', 'mSu', 'R', 'MB, ON, QC', NULL, NULL, NULL, NULL, 'A low growing plant that spreads vigorously on banks, roadsides, and other open areas.  Honeybees use the flowers for nectar and pollen collection.', NULL),
(96, 'Rhododendron groenlandicum', 'Labrador tea', 'N,P', 'eSu, mSu', 'W', 'AB,BC,MB,NF,NT,NS,NU, ON,PE,QC,SK,YT', NULL, NULL, NULL, NULL, NULL, NULL),
(97, 'Ledumgroenlandicum', 'Labrador tea', 'N,P', 'eSu, mSu', 'W', 'AB,BC,MB,NF,NT,NS,NU, ON,PE,QC,SK,YT', NULL, NULL, NULL, NULL, 'Labrador tea grows prolifically in open areas with acidic soils, especially following deforestation.  It produces nectar and pollen that is collected by honeybees in the north.  The honey is reportedly poisono0us to human beings. ', NULL),
(98, 'Leonuriscardiaca', 'Mother wort', 'N,P', 'mSu, lSu, eF', 'R', 'AB, BC, MB, NB, NS, ON, PE, QC, SK', NULL, NULL, NULL, NULL, 'A weedy plant that grows in abundance along roadsides, vacant land, and sometimes in uncultivated fields. A good source of nectar and pollen.', NULL),
(99, 'Lespedeza bicolor', 'Shrub bush-clover', 'N,p', 'mSu, lSu', 'C', 'ON', NULL, NULL, NULL, NULL, 'This is a very attractive nectar plant for bees where it occurs in southern Ontario.', NULL),
(100, 'Liatriscylindracea', 'Blazing star', 'N,P', 'mSu, lSu, eF', 'W', 'ON', NULL, NULL, NULL, NULL, 'This is an attractive nectar and pollen plant for honyebees where it occurs in southern Ontario.', NULL),
(101, 'Ligustrumjaponicum', 'Japanese privet', 'N,P', 'mSu', 'C', 'MB, NS, NB, PE, QC, ON', NULL, NULL, NULL, NULL, 'Privet is grown mostly as hedges.  Its white panicles of flowers produce nectar, but the resulting honey is reputed to taste off.  Honeybees forage for pollen from the flowers.', NULL),
(102, 'Limnanthesdouglasii', 'Meadow foam', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'This plant is highly attractive for its nectar.  It can produce a carpet of bloom in sunny areas when in garden cultivation', NULL),
(103, 'Linaria vulgaris', 'Toadflax', 'N,P', 'mSu, lSu, eF', 'R', 'AB, BC, MB, NB, NF, NS, ON, PE, QC, SK', NULL, NULL, NULL, NULL, 'A widespread weed that produces abundant nectar and pollen.  The deep, tubular flowers are well suited to bumblebees but not for honeybees.', NULL),
(104, 'Linumflavum', 'Yellow flax', 'P', 'mSu', 'C', 'ON', NULL, NULL, NULL, NULL, 'Mostly grown ornamentally.  Honeybees sometimes collect pollen from the flowers.', NULL),
(105, 'Linumperenne', 'Perennial flax', 'N,P', 'mSu, lSu, eF', 'R', 'ON', NULL, NULL, NULL, NULL, 'Mostly grown ornamentally.  Honeybees sometimes collect pollen and nectar from the flowers.', NULL),
(106, 'Linumusitatissimum', 'Flax, Linseed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'This crop plant has flowers that bloom for one day in the morning.  It is generally not regarded as highly valuable for beekeeping.  The flowers produce small amounts of nectar and little pollen (which is pale blue)', NULL),
(107, 'Liriodendron tulipifera', 'Tulip Tree', 'N,P', 'eSp, mSp, lSp, eSu', 'W', 'ON', NULL, NULL, NULL, NULL, 'The large flowers produce an overabundance of nectar in early to mid-summer.  It is a native tree, one of the tallest, of the Carolinian forest of Ontario but highly localized.  It is grown as an ornamental tree.  Although the flowers produce lots of pollen, it seems little gathered by honeybees.  ', NULL),
(108, 'Lonicera spp.', 'Honeysuckles', 'N,P', 'mSu', 'W, some C/R', 'AB,BC,MB,NF,NT,NS,NU, ON,PE,QC,SK,YT', 'images\\Lotus corniculatus_flowers_credit Cara Dawson.jpg', NULL, 'images\\Lotus corniculatus_leaves_credit Cara Dawson.jpg', 'images\\Lotus corniculatus_habitus_credit Cara Dawson.jpg', 'Some honeysuckles have small flowers that produce nectar and pollen abundantly and are well visited by honeybees. Most honeysuckles have deep, tubular flowers that are suited to visits by long-tongued insects like bumblebees and butterflies.  They are also well visited by hummingbirds.  Unless the flowers are pierced by bumblebees, or the nectar rises high enough in the tube, honeybees are unable to obtain it.  ', NULL),
(109, 'Lotus corniculatus', 'Bird’s-foot trefoil', 'N,p', 'mSu, lSu', 'C,W', 'AB, BC, MB, NB, NF, NS, ON, PE, QC, SK, YT', 'images\\Lupinus perennis_flowers_public domain.JPG', NULL, 'images\\Lupinus hirsutus_leaves_copyright Giancarlo Dessi_(CC by-SA 3.0).jpg', 'images\\Lupinus arbustus_habitus_public domain.jpg', 'Grown for ground cover, hay and soil improvement.  It is also naturalized on roadsides and waste lands.  Its  value may vary from place to place and year to year.  It can produce nectar for high quality honey.  Honeybees often gather the pollen.', NULL),
(110, 'Lupinus spp.', 'Lupine', 'P', 'eSu , mSu', 'W, C, R', 'AB, BC, MB, NB, NF, NS, ON, PE, QC, SK', NULL, NULL, NULL, NULL, 'Lupines, wild as well as cultivated and crop species, do not produce nectar.  Honeybees sometimes collect the pollen. ', NULL),
(111, 'Lysimachia spp.', 'Garden loosestrife', 'N,P', 'mSu, lSu, eF', 'R', 'AB,BC,MB,NF,NT,NS,ON,PE,QC,SK,YT', 'images\\Lythrum salicaria_flowers_copyright wiki.user.Christian Fischer_(CC by-SA 3.0).jpg', NULL, 'images\\Lythrum salicaria_leaves_copyright Steven J. Baskauf_bioimages.vanderbilt.edu.jpg', 'images\\Lythrum salicaria_habitus_copyright wiki.user.Meggar_(CC by-SA 3.0).jpg', 'There are several species with yellow flowers that grow in various habitats.  The garden loosestrife is attractive to honeybees for nectar and pollen.', NULL),
(112, 'Lythrumsalicaria', 'Purple loosestrife', 'N,P', 'mSu, lSu', 'R,A', 'AB, BC, MB, NB, NF, NS, ON, PE, QC, SK', NULL, NULL, NULL, NULL, 'A widespread invasive plant of wetlands that supplies abundant nectar and pollen to honeyebees and beekeeping. ', NULL),
(113, 'Malusdomestica', 'Apple', 'N,P', 'eSp, mSp, lSp', 'W,C', 'AB,BC,MB,NF,NT,NS,ON,PE,QC,SK,YT', NULL, NULL, NULL, NULL, 'Apple blossoms are well known as being eagerly worked by honeybees for nectar and pollen.', NULL),
(114, 'Malus coronaria', 'Crab Apple', 'N,P', 'eSp, mSp, lSp', 'W,C', 'AB,BC,MB,NF,NT,NS,ON,PE,QC,SK,YT', 'images\\Malva moschata_flowers_copyright wiki.user.Albert H_(CC by-SA 3.0).jpg', NULL, 'images\\Malva moschata_leaves_copyright wiki.user.Karelj_(CC by-SA 3.0).jpg', 'images\\Malva moschata_habitus_copyright wiki.nl.user.Rasbak_(CC by-SA 3.0).jpg', NULL, NULL),
(115, 'Malvamoschata', 'Musk mallow', 'n,p', 'mSu, lSu, eF, mF', 'R', 'BC, MB, NB, NF, NS, ON, PE, QC', NULL, NULL, NULL, NULL, 'This naturalized plant grows on limestone dominated soils on uncultivated lands and  along roadsides. Honeybees forage on the flowers for nectar and pollen.', NULL),
(116, 'Medicagolupulinus', 'Black medick', 'n,p', 'mSu, lSu, eF', 'R', 'AB,BC,MB,NF,NT,NS,ON,PE,QC,SK,YT', NULL, NULL, NULL, NULL, 'Black medic is not regarded as important of honeybees for either nectar or pollen. ', NULL),
(117, 'Medicago sativa', 'Alfalfa', 'N,p', 'mSu, lSu, eF', 'C', 'AB,BC,MB,NF,NT,NS,ON,PE,QC,SK,YT', NULL, NULL, NULL, NULL, 'Grown extensively for seed and hay.  It is naturalized in open and uncultivated areas. It is a highly valued plant for beekeeping, producing nectar for high quality honey.  Honeybees often tend not to gather the pollen.', NULL),
(118, 'Melilotus alba', 'Sweet Clover White', 'N,P', 'mSu, lSu, eF', 'W,R', 'AB,BC,MB,NF,NT,NS,ON,PE,QC,SK,YT', NULL, NULL, NULL, NULL, 'This plant, in cultivation or growing wild along roadsides and on untended land, is well regard for its value to beekeeping.  It produces abundant nectar valued for the quality of honey that results. The pollen is not so well sought after by honeybees.', NULL),
(119, 'Melilotusindica', 'Small-flowered sweet clover', 'N,P', 'mSu, lSu, eF', 'R', 'MB, NS, BC', NULL, NULL, NULL, NULL, 'This plant, in cultivation or growing wild along roadsides and on untended land, is well regard for its value to beekeeping.  It produces abundant nectar valued for the quality of honey that results. The pollen is not so well sought after by honeybees.', NULL),
(120, 'Melilotusofficinalis', 'Sweet Clover Yellow', 'N,P', 'mSu, lSu, eF', 'W,R', 'AB,BC,MB,NF,NT,NS,ON,PE,QC,SK,YT', NULL, NULL, NULL, NULL, 'This plant, in cultivation or growing wild along roadsides and on untended land, is well regard for its value to beekeeping.  It produces abundant nectar valued for the quality of honey that results. The pollen is not so well sought after by honeybees.', NULL),
(121, 'Mentha spicata', 'Spear mint', 'N,P', 'mSu, lSu, eF', 'R', 'AB, BC, MB, NB, NS, ON, PE, QC, SK', NULL, NULL, NULL, NULL, NULL, NULL),
(122, 'Monardafistulosa', 'Horsemint', 'N,P', 'mSu, lSu, eF, mF', 'W', 'AB, BC, MB, NT, ON, QC, SK', 'images\\Nepeta cataria_flowers_copyright D. G. E. Robertson_(CC by-SA 3.0).jpg', NULL, 'images\\Nepeta cataria_stem,leaves_copyright Forest + Kim Starr_(CC by 3.0).jpg', 'images\\Nepeta cataria_habitus_copyright Forest + Kim Starr_(CC by 3.0).jpg', 'A widespread plant of open areas.  It produced copious nectar that is well collected by honeybees.  The pollen is not easily collected by honeybees because it is retained in the upper “helmet” part of the flowers.  The bergamots are well adapted to pollination by bumblebees.', NULL),
(123, 'Nepetacatarina', 'Catnip', 'N,P', 'mSu, lSu, eF', 'W,R', 'AB, BC, MB, NB, NF, NS, ON, PE, QC, SK', 'images\\Nicotiana tabacum_flowers_copyright wiki.user.Joachim Müllerchen_(CC by 2.5).JPG', NULL, 'images\\Nicotiana tabacum_leaves_credit Cara Dawson.jpg', 'images\\Nicotiana tabacum_habitus_credit Cara Dawson.jpg', 'Primarily a garden plant  but also grows along roadsides and uncultivated areas.  The flowers produce abundant nectar that is gathered extensively by honeybees.  Mints are not known as being important for pollen collection by honeybees.', NULL),
(124, 'Nicotianatabacum', 'Tobacco', 'N,p', 'lSu, eF', 'C', 'BC, ON, QC', 'images\\Oenothera biennis_flowers_copyright Fritz Geller-Grimm_(CC by-SA 2.5).jpg', NULL, 'images\\Oenothera biennis_leaves_public domain.jpg', 'images\\Oenothera biennis_habitus_copyright Bodner, Miller and Miller. 2005.  Forest plants of the southeast and their wildlife uses_plants.usda.gov_Used With Permission.jpg', 'This crop plant is usually cut before it fully blooms.  Its deep tubular flowers hide the abundant nectar, but honeybees sometimes obtain it through punctures in the base of the flowers made by some kinds of bumblebees.  It is not an important plant for beekeepers.', NULL),
(125, 'Oenotherabiennis', 'Evening primrose', 'n', 'mSu, lSu, eF', 'W,R', 'AB, BC, MB, NB, NF, NS, ON, PE, QC, SK', 'images\\Onobrychis viciifolia_flower_public domain.jpg', NULL, 'images\\Onobrychis viciifolia_leaves,flowers_copyright Flickr.Dandelion And Burdock_(CC by-NC-SA 2.0).jpg', 'images\\Onobrychis viciifolia_habitus_copyright wiki.user.Bernd Haynold_(CC by-SA 3.0).jpg', 'Evening primroses produce large amounts of nectar, but it is not easily accessible for honeybees.  They also produce pollen copiously, but it forms strings on viscin threads.  They are not regarded as iumportant to honeybees or beekeeping.', NULL),
(126, 'Onobrychis viciifolia', 'Sainfoin', 'N, P', 'mSu', 'QC-West', 'MB, ON, QC, SK, YT, AB, BC', 'images\\Oxalis stricta_flowers,fruit_credit Cara Dawson.JPG', NULL, 'images\\Oxalis stricta_leaves_copyright Steven J. Baskauf_bioimages.vanderbilt.edu.jpg', 'images\\Oxalis stricta_habitus_credit Cara Dawson.JPG', NULL, NULL),
(127, 'Oxalis stricta', 'Wood sorrel', 'N,P', 'eSu, mSu, lSu, eF', 'R', 'AB, BC, MB, NB, NF, NS, ON, PE, QC, SK', 'images\\Paeonia sp._flower_copyright Bob Gutowski_(CC by 2.0).jpg', 'images\\Paeonia sp._fruit_copyright H Zell_(CC by-SA 3.0).JPG', NULL, 'images\\Paeonia sp._habitus_copyright Franz Xaver_(CC by-SA 3.0).jpg', NULL, NULL),
(128, 'Paeonia', 'Peony', 'n, p', 'eSu, mSu', 'C', 'AB, BC, MB, NB, NF, NS, ON, PE, QC, SK', 'images\\Papaver sp._flowers, leaves_copyright user.wiki.Jerzy Opiola_(CC by-SA 3.0).jpg', 'images\\Papaver sp._fruit_copyright Magnus Manske_(CC by-SA 3.0).JPG', NULL, 'images\\Papaver sp._habitus_public domain.JPG', NULL, NULL),
(129, 'Papaver spp.', 'Poppy', 'P', 'mSu', 'C,W', 'AB,BC,MB,NF,NT,NS,NU, ON,PE,QC,SK,YT', 'images\\Pastinaca sativa_flowers_copyright wiki.user.MDarkone_(CC by-SA 2.5).JPG', NULL, 'images\\Pastinaca sativa_leaves_copyright user.wiki.Goldlocki_(CC by-SA 3.0).jpg', 'images\\Pastinaca sativa_habitus_copyright Flickr.dix-tuin_(CC by-NC-SA 2.0).jpg', NULL, NULL),
(130, 'Pastinaca sativa', 'Wild parsnip', 'n,p', 'mSu, lSu, eF', 'R', 'AB, BC, MB, NB, NF, NS, NT, ON, PE, QC, SK, YT', NULL, NULL, NULL, NULL, NULL, NULL),
(131, 'Penstemon gracilis', 'Slender penstemon', 'N,P', 'mSu', 'W', 'AB, BC, MB, ON, SK', NULL, NULL, NULL, NULL, 'This is a super special plant', NULL),
(132, 'Phacelia linearis ', 'Scorpion weed, Phacelia', 'N,P', 'eSp,mSp, lSp,eSu,mSu,lSu,eF, mF,lF', 'W', 'AB, BC', NULL, NULL, NULL, NULL, NULL, NULL),
(133, 'Phaseolus coccineus', 'Scarlet runner bean', 'N,P', 'lSu', 'C', 'AB, BC, MB, NB, NF, NS, ON, PE, QC, SK', NULL, NULL, NULL, NULL, NULL, NULL),
(134, 'Phaseolus vulgaris', 'Garden Bean', 'N,P', 'mSu', 'C', 'AB, BC, MB, NB, NF, NS, ON, PE, QC, SK', NULL, NULL, NULL, NULL, NULL, NULL),
(135, 'Phryma leptostachya', 'Lop seed', 'n,p', 'mSu, lSu, eF', 'W', 'MB, NB, ON, QC', NULL, NULL, NULL, NULL, NULL, NULL),
(136, 'Physostegia virginiana', 'Obedient plant', 'N,P', 'mSu', 'W,C', 'AB, BC, MB, NB, NF, NS, ON, PE, QC, SK', NULL, NULL, NULL, NULL, NULL, NULL),
(137, 'Picea spp.', 'Spruces', 'Hd, r', 'eSu', 'W', 'AB,BC,MB,NF,NT,NS,NU, ON,PE,QC,SK,YT', NULL, NULL, NULL, NULL, NULL, NULL),
(138, 'Polygonum hydropiperoides and other spp.', 'Smartweed', 'N,P', 'mSu', 'W', 'MB, NB, NF, NS, ON, PE, QC, SK', NULL, NULL, NULL, NULL, 'This is a super special plant', NULL),
(139, 'Populus spp.', 'Aspen', 'P, r', 'eSp, mSp, lSp', 'W,c', 'AB,BC,MB,NF,NT,NS,ON,PE,QC,SK,YT', NULL, NULL, NULL, NULL, NULL, NULL),
(140, 'Prunella vulgaris', 'Self-heal', 'N,P', 'mSu, lSu, eF', 'R,W', 'AB, BC, MB, NB, NF, NS, ON, PE, QC, SK, YT', 'images\\Prunus armeniaca (Apricot)_flowers_copyright wiki.user.Victor Vizu_(CC by-SA 3.0).jpg', NULL, 'images\\Prunus armeniaca (Apricot)_leaves, fruit_copyright wiki.user.Apple2000_(CC by-SA 3.0).jpg', 'images\\Prunus armeniaca (Apricot)_habitus_copyright Flickr.HermannFalkner[slash]sokol_(CC by-NC-SA 2.0).jpg', 'v', NULL),
(141, 'Prunus armeniaca', 'Apricot', 'N, P', 'eSp, mSp, lSp', 'C', 'ON, QC', 'images\\Prunus cerasus (Sour cherry)_flowers_public domain.jpg', 'images\\Prunus cerasus (Sour cherry)_fruit_shutterstock.png', NULL, 'images\\Prunus cerasus (Sour cherry)_habitus_copyright wiki.user.Prazak_(CC by-SA 3.0).jpg', NULL, NULL),
(142, 'Prunus cerasus', 'Sour cherry', 'N,P', 'mSu', 'C', 'BC, NB, NS, ON, PE, QC', NULL, NULL, NULL, NULL, 'This is a super special plant', NULL),
(143, 'Prunus domestica', 'Plum', 'N,P', 'eSp, mSp, lSp', 'C', 'BC, NB, NF, NS, ON, QC', NULL, NULL, NULL, NULL, 'This is a super special plant', NULL),
(144, 'Prunus pensylvanica', 'Pin Cherry', 'N, P', 'eSp', 'W', 'AB,BC,MB,NF,NT,NS,ON,PE,QC,SK,YT', NULL, NULL, NULL, NULL, 'This is a super special plant', NULL),
(145, 'Prunus persica', 'Peach', 'N,P', 'eSp, mSp, lSp', 'C', 'BC, ON', NULL, NULL, NULL, NULL, NULL, NULL),
(146, 'Prunus serotina', 'Wild Cherry', 'N,P', 'lSp, eSu', 'W,R', 'BC, NB, NS, ON, QC', NULL, NULL, NULL, NULL, NULL, NULL),
(147, 'Prunus serrulata', 'Japanese cherry', 'N,P', 'eSu', 'C', 'ON, BC', NULL, NULL, NULL, NULL, NULL, NULL),
(148, 'Ptelea trifoliata', 'Hop tree', 'N,P', 'mSu', 'W', 'ON', NULL, NULL, NULL, NULL, NULL, NULL),
(149, 'Sorbus aucuparia', 'Rowan tree, Mountain ash', 'N,P', 'eSu', 'C,R', 'AB, BC, MB, NB, NF, NS, ON, PE, QC, SK', '\\images\\Pyrus communis.JPG', NULL, NULL, NULL, 'This is a super special plant', NULL),
(150, 'Pyrus communis', 'Pear', 'n,P', 'eSp, mSp, lSp', 'C', 'BC, NB, NS, ON, QC', NULL, NULL, NULL, NULL, NULL, NULL),
(151, 'Quercus spp.', 'Oak', 'P,Hd', 'eSp, mSp, lSp, eSu, mSu, lSu', 'W,C', 'AB,BC,MB,NF,NT,NS,ON,PE,QC,SK,YT', NULL, NULL, NULL, NULL, NULL, NULL),
(152, 'Raphanus raphanistrum ', 'Radish', 'N,P', 'mSu, lSu', 'R,W', 'AB, BC, MB, NB, NF, NS, ON, PE, QC, SK', NULL, NULL, NULL, NULL, NULL, NULL),
(153, 'Rhamnus alnifolia', 'Alder leaved Buckthorn', 'N,P', 'mSu', 'Wa', 'AB, BC, MB, NB, NF, NS, ON, PE, QC, SK', NULL, NULL, NULL, NULL, NULL, NULL),
(154, 'Rhamnus cathartica', 'Buckthorn', 'N,P', 'mSu', 'W', 'AB, MB, NB, NF, NS, ON, PE, QC, SK', NULL, NULL, NULL, NULL, NULL, NULL),
(155, 'Rhododendron canadense', 'Rhodora', 'N,P', 'mSu', 'W', 'LB, NB, NF, NS, ON, PE, QC', NULL, NULL, NULL, NULL, NULL, NULL),
(156, 'Rhus spp.', 'Sumacs, Poison Ivy & Oak', 'N,P', 'mSu', 'W,R', 'AB, BC, MB, NB, NS, ON, PE, QC, SK', NULL, NULL, NULL, NULL, NULL, NULL),
(157, 'Ribes spp.', 'Currants (various species across Canada)', 'N,P', 'eSu, mSu', 'W,C', 'AB,BC,MB,NF,NT,NS,NU, ON,PE,QC,SK,YT', 'images\\Robinia pseudoacacia_flowers_copyright wiki.user.Mehrajmir13_(CC by-SA 3.0).jpg', 'images\\Robinia pseudoacacia_fruit_copyright Steven J. Baskauf_bioimages.vanderbilt.edu.jpg', 'images\\Robinia pseudoacacia_leaves_copyright Steven J. Baskauf_bioimages.vanderbilt.edu.jpg', 'images\\Robinia pseudoacacia_habitus_public domain.jpg', NULL, NULL),
(158, 'Robinia pseudoacacia', 'Black Locust', 'N,P', 'mSu', 'C,W', 'BC, NB, NS, ON, PE, QC', 'images\\Rosa sp._flower,fruit_copyright Flickr.mmmavocado_(CC by 2.0).jpg', 'images\\Rosa sp._stem_copyright user.wiki.Midori_(CC by-SA 3.0).JPG', 'images\\Rosa sp._leaves_copyright Steven J. Baskauf_bioimages.vanderbilt.edu.jpg', 'images\\Rosa sp._habitus_copyright Steven J. Baskauf_bioimages.vanderbilt.edu.jpg', NULL, NULL),
(159, 'Rosa spp.', 'Wild roses', 'P', 'mSu', 'W', 'AB,BC,MB,NF,NT,NS,NU, ON,PE,QC,SK,YT', NULL, NULL, NULL, NULL, NULL, NULL),
(160, 'Rubus fruticosus and other spp.', 'Blackberry', 'N,P', 'mSu', 'C,W', 'AB,BC,MB,NF,NT,NS,NU, ON,PE,QC,SK,YT', NULL, NULL, NULL, NULL, NULL, NULL),
(161, 'Rubus idaeus', 'Raspberry', 'N,P', 'mSu', 'C,W,R', 'AB,BC,MB,NF,NT,NS,NU, ON,PE,QC,SK,YT', NULL, NULL, NULL, NULL, NULL, NULL),
(162, 'Rubus occidentalis', 'Raspberry', 'N,P', 'mSu, lSu', 'C,W,R', 'AB, BC, MB, NB, NF, NS, NT, NU, ON, PE, QC, SK, YT', NULL, NULL, NULL, NULL, NULL, NULL),
(163, 'Rubus odoratus', 'Purple Flowered Raspberry, Thimbleberry', 'N,P', 'mSu', 'W,R', 'NB, NS, ON, QC', 'images\\Rudbeckia hirta_flowers_credit Cara Dawson.JPG', NULL, 'images\\Rudbeckia hirta_leaves_copyright Steven J. Baskauf_bioimages.vanderbilt.edu.jpg', 'images\\Rudbeckia hirta_habitus_copyright Steven J. Baskauf_bioimages.vanderbilt.edu.jpg', NULL, NULL),
(164, 'Rudbeckia hirta', 'Cone Flower', 'N,P', 'mSu, lSu, eF, mF', 'W,R', 'AB, BC, MB, NB, NF, NS, ON, PE, QC, SK', '\\images\\Salix spp..jpg', NULL, NULL, NULL, 'test', NULL),
(165, 'Salix spp.', 'Willows', 'N,P', 'eSp', 'W,R.c', 'AB,BC,MB,NF,NT,NS,ON,PE,QC,SK,YT', 'images\\Salvia officinalis_flowers_copyright user.wiki.Duk_(CC by-SA 3.0).JPG', NULL, 'images\\Salvia officinalis_leaf_copyright user.wiki.Takkk_(CC by-SA 3.0).jpg', 'images\\Salvia officinalis_habitus_copyright Flickr.andreasbalzer_(CC by-NC-SA 2.0).jpg', NULL, NULL);
INSERT INTO `main` (`ID`, `Scientific_Name`, `Common_Name`, `Bee_Resource`, `Season`, `Plant_Type`, `Location`, `image_flower`, `image_fruit`, `image_leaves`, `image_habitus`, `info_text`, `data`) VALUES
(166, 'Salvia officinalis', 'Sage', 'N, P', 'mSu', 'C', 'AB,BC,MB,NF,NT,NS,NU, ON,PE,QC,SK,YT', NULL, NULL, NULL, NULL, NULL, NULL),
(167, 'Sambucus canadensis', 'Elderberry', 'n, p', 'mSu', 'C,W,R', 'MB, NB, NS, ON, PE, QC', '\\images\\Sanguinaria canadensis.jpg', NULL, NULL, NULL, 'This is a super special plant', NULL),
(168, 'Sanguinaria canadensis', 'Bloodroot', 'P', 'eSp, mSp, lSp', 'W', 'MB, NB, NS, ON, QC', NULL, NULL, NULL, NULL, NULL, NULL),
(169, 'Scrophularia marilandica', 'Carpenter''s square', 'N', 'mSu', 'East', 'ON, QC', NULL, NULL, NULL, NULL, NULL, NULL),
(170, 'Senecio spp.', 'Groundsel', 'N,P', 'mSu, lSu', 'R, W', 'AB,BC,MB,NF,NT,NS,NU, ON,PE,QC,SK,YT', NULL, NULL, NULL, NULL, NULL, NULL),
(171, 'Sinapis alba', 'White mustard', 'N. P', 'mSu, lSu, eF, mF', 'C,R', 'AB, BC, MB, NB, NS, ON, PE, QC, SK, YT', NULL, NULL, NULL, NULL, NULL, NULL),
(172, 'Solidago canadensis & other spp.', 'Goldenrod', 'N,P', 'lSu,eF,mF,lF', 'W,R', 'AB, BC, MB, NB, NF, NS, NT, NU, ON, PE, QC, SK, YT', NULL, NULL, NULL, NULL, NULL, NULL),
(173, 'Sonchus spp.', 'Sow Thistle', 'N,P', 'mSu, lSu', 'R', 'AB, BC, MB, NB, NF, NS, NT, ON, PE, QC, SK, YT', NULL, NULL, NULL, NULL, NULL, NULL),
(174, 'Stachys spp.', 'Hedgenettle', 'N, P', 'lSp', 'W', 'AB,BC,MB,NF,NT,NS,ON,PE,QC,SK,YT', NULL, NULL, NULL, NULL, NULL, NULL),
(175, 'Stachys palustris', 'Marsh hedge-nettle', 'N,P', 'mSu', 'W,A', 'MB, NB, NF, NS, ON, PE, QC', NULL, NULL, NULL, NULL, NULL, NULL),
(176, 'Symphyotrichum spp.', 'Asters (numerous species)', 'N,P', 'lSu,eF,mF,lF', 'W', 'AB, BC, MB, NB, NF, NS, NT, NU, ON, PE, QC, SK, YT', '\\images\\Symplocarpus foetidus.jpg', NULL, NULL, NULL, 'cc', NULL),
(177, 'Symplocarpus foetidus', 'Skunk Cabbage', 'P', 'eSp', 'W,A', 'NB, NS, ON, QC', NULL, NULL, NULL, NULL, NULL, NULL),
(178, 'Taraxacum officinale', 'Dandelion', 'N,P', 'eSp, mSp, lSp, eSu', 'R', 'AB,BC,MB,NF,NT,NS,NU, ON,PE,QC,SK,YT', NULL, NULL, NULL, NULL, NULL, NULL),
(179, 'Thalictrum spp.', 'Meadow rue', 'P', 'mSp, lSp, eSu, mSu, lSu', 'W,R', 'AB, BC, MB, NB, NF, NS, ON, PE, QC, SK', NULL, NULL, NULL, NULL, NULL, NULL),
(180, 'Thymus', ' Thyme', 'N,p', 'lSu', 'C', 'AB, BC, MB, NB, NF, NS, ON, PE, QC, SK', NULL, NULL, NULL, NULL, NULL, NULL),
(181, 'Tilia americana', 'Basswood', 'N,P', 'mSu', 'W', 'MB, ON , QC, PE, NS, NB', NULL, NULL, NULL, NULL, NULL, NULL),
(182, 'Tragopogon dubius', 'Salsify', 'n, p', 'mSu', 'R', 'AB,BC,MB,NF,NT,NS,ON,PE,QC,SK,YT', NULL, NULL, NULL, NULL, NULL, NULL),
(183, 'Trifolium hybridum', 'Alsike Clover', 'N,P', 'mSu', 'C', 'AB,BC,MB,NF,NT,NS,ON,PE,QC,SK,YT', NULL, NULL, NULL, NULL, NULL, NULL),
(184, 'Trifolium pratense', 'Red Clover', 'n,P', 'mSu', 'C,W,R', 'AB,BC,MB,NF,NT,NS,ON,PE,QC,SK,YT', NULL, NULL, NULL, NULL, NULL, NULL),
(185, 'Trifolium repens', 'White Clover', 'N,P', 'mSu', 'C,W,R', 'AB,BC,MB,NF,NT,NS,ON,PE,QC,SK,YT', NULL, NULL, NULL, NULL, NULL, NULL),
(186, 'Trifolium spp.', 'Clovers', 'N,P', 'mSu', 'C,R', 'AB,BC,MB,NF,NT,NS,NU, ON,PE,QC,SK,YT', '\\images\\Ulmus americana.jpg', NULL, NULL, NULL, 'This is a super special plant', NULL),
(187, 'Ulmus americana', 'Elm', 'P', 'eSp, mSp, lSp', 'W', 'MB, NB, NS, ON, PE, QC, SK', NULL, NULL, NULL, NULL, NULL, NULL),
(188, 'Vaccinium spp.', 'Blueberry (several species)', 'N', 'mSu', 'C,W', 'AB,BC,MB,NF,NT,NS,NU, ON,PE,QC,SK,YT', NULL, NULL, NULL, NULL, NULL, NULL),
(189, 'Verbena spp.', 'Vervain', 'n,p', 'mSu, lSu, eF, mF', 'W', 'AB, BC, MB, NB, NS, ON, QC, SK', NULL, NULL, NULL, NULL, NULL, NULL),
(190, 'Viburnum lentago', 'Nanny berry', 'n, P', 'eSu, mSu', 'W', 'MB, NB, ON, QC, SK', NULL, NULL, NULL, NULL, NULL, NULL),
(191, 'Viburnum opulus ', 'European cranberry bush', 'n, P', 'mSu', 'C,R', 'AB, BC, MB, NB, NF, NS, ON, PE, QC, SK', NULL, NULL, NULL, NULL, NULL, NULL),
(192, 'Vicia americana', 'American Vetch', 'N,P', 'eSu, mSu', 'W', 'AB, BC, MB, NT, ON, QC, SK, YT', NULL, NULL, NULL, NULL, NULL, NULL),
(193, 'Vicia angusifolia', 'Common Vetch', 'N,P', 'mSu, lSu, eF, mF', 'R', 'BC, MB, NB, NF, NS, ON, PE, QC, YT', NULL, NULL, NULL, NULL, NULL, NULL),
(194, 'Vicia sativa', 'Common Vetch', 'N,P', 'mSu, lSu, eF, mF', 'R', 'BC, MB, NB, NF, NS, ON, PE, QC, YT', NULL, NULL, NULL, NULL, NULL, NULL),
(195, 'Vicia cracca', 'Cow vetch', 'N, P', 'eSu, mSu, lSu', 'R', 'AB,BC,MB,NF,NT,NS,NU, ON,PE,QC,SK,YT', NULL, NULL, NULL, NULL, NULL, NULL),
(196, 'Vicia faba', 'Broad bean', 'N, P', 'lSu', 'C', 'AB, BC, MB, NB, NF, NS, ON, PE, QC, SK', NULL, NULL, NULL, NULL, NULL, NULL),
(197, 'Vicia lathyroides', 'Spring vetch', 'N, P', 'mSu', 'C', 'BC, MB, NB, NF, NS, ON, PE, QC, YT', NULL, NULL, NULL, NULL, NULL, NULL),
(198, 'Vicia villosa', 'Hairy vetch', 'N,P', 'mSu, lSu, eF', 'C', 'BC, MB, ON, QC, NB, PE, NS', NULL, NULL, NULL, NULL, NULL, NULL),
(199, 'Vigna sinensis', 'Cow pea', 'N,P', 'mSu', 'C', 'ON', NULL, NULL, NULL, NULL, NULL, NULL),
(200, 'Viola spp.', 'Violets & Pansy', 'N, P', 'eSp, mSp, lSp, eSu, mSu', 'W, C, R', 'AB,BC,MB,NF,NT,NS,ON,PE,QC,SK,YT', NULL, NULL, NULL, NULL, NULL, NULL),
(201, 'Vitis spp.', 'Grapes', 'P', 'mSu', 'C,W,R', 'MB, NB, NS, ON, PE, QC', NULL, NULL, NULL, NULL, NULL, NULL),
(202, 'Wisteria floribunda', 'Wisteria', 'N, P', 'eSu', 'C', 'BC, ON', NULL, NULL, NULL, NULL, NULL, NULL),
(203, 'Zanthoxylum americanum', 'Prickly Ash', 'N,P', 'eSp, mSp', 'W', 'ON, QC', NULL, NULL, NULL, NULL, NULL, NULL),
(204, 'Zea mays', 'Maize', 'P', 'mSu', 'C', 'ON, QC,MB', NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `season`
--

CREATE TABLE IF NOT EXISTS `season` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Season_Code` varchar(16) NOT NULL,
  `Season_Description` varchar(32) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `season`
--

INSERT INTO `season` (`ID`, `Season_Code`, `Season_Description`) VALUES
(1, 'Sp', 'Spring'),
(2, 'Su', 'Summer'),
(3, 'F', 'Fall');

-- --------------------------------------------------------

--
-- Table structure for table `subseason`
--

CREATE TABLE IF NOT EXISTS `subseason` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Subseason_Code` varchar(16) NOT NULL,
  `Subseason_Description` varchar(32) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `subseason`
--

INSERT INTO `subseason` (`ID`, `Subseason_Code`, `Subseason_Description`) VALUES
(1, 'e', 'Early'),
(2, 'm', 'Mid'),
(3, 'l', 'Late'),
(4, '', 'Full');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
