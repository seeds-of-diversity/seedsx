-- phpMyAdmin SQL Dump
-- version 2.8.0.1
-- http://www.phpmyadmin.net
-- 
-- Host: custsql-ipg15.eigbox.net
-- Generation Time: May 19, 2013 at 09:05 PM
-- Server version: 5.0.91
-- PHP Version: 4.4.9
-- 
-- Database: `plant`
-- 
CREATE DATABASE `plant` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `plant`;

-- --------------------------------------------------------

-- 
-- Table structure for table `bee`
-- 

CREATE TABLE `bee` (
  `ID` int(11) NOT NULL auto_increment,
  `code` varchar(8) NOT NULL,
  `bee` varchar(32) NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

-- 
-- Dumping data for table `bee`
-- 

INSERT INTO `bee` VALUES (1, 'P', 'Pollen');
INSERT INTO `bee` VALUES (2, 'N', 'Nectar');
INSERT INTO `bee` VALUES (3, 'Hd', 'Honeydew');
INSERT INTO `bee` VALUES (4, 'r', 'Resin');

-- --------------------------------------------------------

-- 
-- Table structure for table `location`
-- 

CREATE TABLE `location` (
  `ID` int(11) NOT NULL auto_increment,
  `Location_Code` varchar(32) NOT NULL,
  `Location_Description` varchar(32) NOT NULL,
  `Active_Province` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=latin1 AUTO_INCREMENT=14 ;

-- 
-- Dumping data for table `location`
-- 

INSERT INTO `location` VALUES (1, 'AB', 'Alberta', 1);
INSERT INTO `location` VALUES (2, 'BC', 'British Columbia', 1);
INSERT INTO `location` VALUES (3, 'MB', 'Manitoba', 1);
INSERT INTO `location` VALUES (4, 'NB', 'New Brunswick', 1);
INSERT INTO `location` VALUES (5, 'NF', 'Newfoundland & Labrador', 1);
INSERT INTO `location` VALUES (6, 'NT', 'Northwest Territories', 1);
INSERT INTO `location` VALUES (7, 'NS', 'Nova Scotia', 1);
INSERT INTO `location` VALUES (8, 'NU', 'Nunavut', 1);
INSERT INTO `location` VALUES (9, 'ON', 'Ontario', 1);
INSERT INTO `location` VALUES (10, 'PE', 'Prince Edward Island', 1);
INSERT INTO `location` VALUES (11, 'QC', 'Quebec', 1);
INSERT INTO `location` VALUES (12, 'SK', 'Saskatchewan', 1);
INSERT INTO `location` VALUES (13, 'YT', 'Yukon', 1);

-- --------------------------------------------------------

-- 
-- Table structure for table `main`
-- 

CREATE TABLE `main` (
  `ID` int(11) NOT NULL auto_increment,
  `Scientific_Name` varchar(128) character set utf8 default NULL,
  `Common_Name` varchar(128) character set utf8 default NULL,
  `Bee_Resource` varchar(32) character set utf8 default NULL,
  `Season` varchar(64) character set utf8 default NULL,
  `Plant_Type` varchar(45) character set utf8 default NULL,
  `Location` varchar(128) character set utf8 default NULL,
  `image_flower` varchar(256) character set utf8 default NULL,
  `image_fruit` varchar(256) character set utf8 default NULL,
  `image_leaves` varchar(256) character set utf8 default NULL,
  `image_habitus` varchar(256) character set utf8 default NULL,
  `info_text` text character set utf8,
  `data` varchar(128) character set utf8 default NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=275 DEFAULT CHARSET=latin1 AUTO_INCREMENT=275 ;

-- 
-- Dumping data for table `main`
-- 

INSERT INTO `main` VALUES (1, 'Abies spp.', '', '', '', '', '', '', '', '', '', 'The firs may be used by honeybees if honeydew is being produced by sap-sucking insects (e.g. aphids, scale insects).  Honeybees use spruce gum for propolis.', '');
INSERT INTO `main` VALUES (2, 'Acer negundo', 'Box Elder', 'P', 'eSp', 'W', 'AB,BC,MB,NT,NS,ON,PE,QC,SK,YT', '', '', '', 'Acer negundo_flowers_copyright Flickr.Putneypics.jpg', 'This early spring blooming tree does not produce nectar.  Only the male trees produce pollen, copiously, and are worked extensively by honeybees in spring.', '');
INSERT INTO `main` VALUES (3, 'Acer saccharinum', 'Silver Maple', 'P', 'eSp', 'W', 'AB,BC,MB,NT,NS,ON,PE,QC,SK,YT', '', '', '', 'Acer saccharinum_flower_copyright Steven J. Baskauf_bioimages.vanderbilt.edu.jpg', 'The early spring blooms of this widespread tree of eastern Canada can be an important source of nectar.  It is not known by beekeepers for pollen production.', '');
INSERT INTO `main` VALUES (4, 'Alnus spp.', 'Alder', '', 'eSp', 'W', 'AB,BC,MB,NF,NT,NS,ON,PE,QC,SK,YT', '', '', '', '', 'These early blooming shrubs and treelets of wet areas, along streams and ditches, around ponds and lakes produce copious amounts of pollen in spring.  Although the pollen is not protein rich, it is eagerly collected by honeybees and considered valuable by beekeepers.', '');
INSERT INTO `main` VALUES (5, 'Anenome patens', 'Prairie crocus', 'P', 'eSp', 'W', 'AB,BC,MB,NT,ON,SK,YT', '', '', '', 'Anemone patens_flowers_copyright user.wiki.Jerzy Strzelecki_(CC by-SA 3.0).jpg', 'Grows in open grassy areas.  A good source of pollen early in the year.', '');
INSERT INTO `main` VALUES (6, 'Carya spp.', 'Hickory', '', 'eSp', 'W', 'ON,QC', '', '', '', '', 'These early blooming woodland trees  (hickory) produce large amounts of pollen in early spring.  The pollen is probably not protein rich, but can be eagerly collected by honeybees when no other pollen sources are available.', '');
INSERT INTO `main` VALUES (7, 'Rosa spp.', 'Wild roses', 'P', 'mSu', 'W', 'AB,BC,MB,NF,NT,NS,NU,ON,PE,QC,SK,YT', '', '', '', '', 'Canada’s native roses are important sources of pollen for honeybees in early and mid-summer.  The flowers do not produce nectar.', '');
INSERT INTO `main` VALUES (23, 'Aesculus hippocastanum', 'Horse Chestnut', 'P,N,r', 'lSp,eSu', 'W', 'BC,NB,NS,ON,QC', '', '', '', 'Aesculus hippocastanum_flowers_copyright Flickr.blumenbiene_(CC by 2.0).jpg', 'This well-known ornamental tree produces great amounts of nectar that is eagerly collected by honeybees, as is the pollen.  Both nectar and pollen have been reported to be toxic to honeybees when other forage is not available.', '');
INSERT INTO `main` VALUES (11, 'Salix spp.', 'Willows', 'P,N', 'eSp', 'W,R', 'AB,BC,MB,NF,NT,NS,ON,PE,QC,SK,YT', '', '', '', 'Salvia officinalis_flowers_copyright user.wiki.Duk_(CC by-SA 3.0).JPG', 'All across Canada there are large stands of numerous species of willow.  They are very important sources of nectar (both sexes of plant) and pollen (from the male plants) in the spring for honeybees and beekeeping', '');
INSERT INTO `main` VALUES (12, 'Caragana arborescens', 'Caragana', 'P,N', 'eSu', 'C', 'AB,BC,MB,NB,NT,ON,QC,SK,YT', '', '', '', 'Caragana arborescens_flowers_copyright Andrew Butko_(CC by-SA 3.0).jpg', 'These smallish trees of open areas produce nectar and pollen in abundance.  It is a major source of nectar and important in the early summer honey flow is some places. The flowers are also well visited by bumblebees for both pollen and nectar.', '');
INSERT INTO `main` VALUES (13, 'Sorbus aucuparia', 'Rowan tree, Mountain ash', 'P,N', 'eSu', 'C,R', 'AB,BC,MB,NB,NF,NS,ON,PE,QC,SK', '', '', '', '', '', '');
INSERT INTO `main` VALUES (14, 'Ambrosia trifida', 'Giant Ragweed', 'P', 'mSu', 'R', 'AB,MB,NB,NS,ON,PE,QC,SK', '', '', '', 'Ambrosia trifida_flowers_copyright Le.Loup.Gris_(CC by-SA 3.0).jpg', 'This aggressive weed of cultivated fields, especially at the edges is a source of pollen sometimes collected by honeybees.', '');
INSERT INTO `main` VALUES (15, 'Asclepias syriaca', 'Milkweed', 'N', 'mSu', 'W,R', 'AB,BC,MB,NB,NF,NS,ON,PE,QC,SK', '', '', '', 'Asclepias syriaca_flowers_copyright Steven J. Baskauf_bioimages.vanderbilt.edu.jpg', 'Field milkweed is an excellent source of nectar.  It grows abundantly, sometimes in extensive clones along roadsides and both cultivated and uncultivated lands.  Its pollen is produced in packets (pollinia) and is not collected by honeybees, except accidentally as the pollinia attach to the bees’ legs.', '');
INSERT INTO `main` VALUES (16, 'Lonicera spp.', 'Honeysuckles', 'P,N', 'mSu', 'W,C,R', 'AB,BC,MB,NB,NF,NT,NS,NU,ON,PE,QC,SK,YT', '', '', '', 'Lotus corniculatus_flowers_credit Cara Dawson.jpg', 'Some honeysuckles have small flowers that produce nectar and pollen abundantly and are well visited by honeybees. Most honeysuckles have deep, tubular flowers that are suited to visits by long-tongued insects like bumblebees and butterflies.  They are also well visited by hummingbirds.  Unless the flowers are pierced by bumblebees, or the nectar rises high enough in the tube, honeybees are unable to obtain it.', '');
INSERT INTO `main` VALUES (17, 'Onobrychis viciaefolia', 'Sainfoin', 'P,N', 'mSu', 'C', 'AB,BC,MB,ON,QC,SK,YT', '', '', '', 'Oxalis stricta_flowers,fruit_credit Cara Dawson.JPG', 'Grown for hay. It is a valued plant for beekeeping, producing nectar for high quality honey.  Honeybees often tend not to gather the pollen.', '');
INSERT INTO `main` VALUES (18, 'Papaver spp.', 'Poppy', 'P', 'mSu', 'W,C', 'AB,BC,MB,NF,NT,NS,NU,ON,PE,QC,SK,YT', '', '', '', 'Pastinaca sativa_flowers_copyright wiki.user.MDarkone_(CC by-SA 2.5).JPG', 'Poppies produce little nectar, if any, but the dark pollen is attractive to honeybees.  It is suggested that pollen may be narcotic to bees.', '');
INSERT INTO `main` VALUES (19, 'Robinia pseudoacacia', 'Black Locust', 'P,N', 'mSu', 'W,C', 'AB,BC,MB,NF,NT,NS,NU,ON,PE,QC,SK,YT', '', '', '', 'Rosa sp._flower,fruit_copyright Flickr.mmmavocado_(CC by 2.0).jpg', 'Although this tree is appreciated by beekeepers for its value in honey production and providing pollen for honeybees, some cultivars are better than others. It is native to the eastern USA but well established as an ornamental and naturalized in southern Canada.', '');
INSERT INTO `main` VALUES (20, 'Rubus odoratus', 'Purple Flowered Raspberry, Thimbleberry', 'P,N', 'mSu', 'W,R', 'NB,NS,ON,QC', '', '', '', 'Rudbeckia hirta_flowers_credit Cara Dawson.JPG', 'Quite a common plant of forest margins.  It is source of nectar and pollen for many insects, including for honeybees and beekeeping.', '');
INSERT INTO `main` VALUES (21, 'Sambucus canadensis', 'Elderberry', 'P,N', 'mSu', 'W,C,R', 'MB,NB,NS,ON,PE,QC', '', '', '', '', 'Although this shrub flowers abundantly and produces nectar and pollen, it is not used extensively by honeybees.', '');
INSERT INTO `main` VALUES (22, 'Trifolium spp.', 'Clovers', 'P,N', 'mSu', 'C,R', 'AB,BC,NB,NF,NT,NS,NU,ON,PE,QC,SK,YT', '', '', '', '', '', '');
INSERT INTO `main` VALUES (25, 'Caltha palustris', 'Marsh marigold', '', '', '', '', '', '', '', 'Caltha palustris_flowers_copyright J.Dennett.jpg', 'Flowers in wet and flooded woodland areas early in spring.  Its main importance for beekeeping is as a spring pollen provider.', '');
INSERT INTO `main` VALUES (26, 'Cichorium intybus', 'Chicory', 'P,N', 'mSu,lSu,eF', 'R', 'AB,BC,MB,NB,NF,NS,ON,PE,QC,SK', '', '', '', 'Cichorium intybus_flower_credit Cara Dawson.jpg', 'A weedy plant that grows along roadsides, vacant land, and sometimes in uncultivated fields. Flowers tend to close in the afternoon. A good source of nectar and pollen.', '');
INSERT INTO `main` VALUES (27, 'Eupatorium perfoliatum', 'Boneset', 'P,N', 'mSu,lSu,eF,mF', 'W', 'MB,NB,NS,ON,PE,QC', '', '', '', 'Euphorbia esula_flowers_credit Cara Dawson.JPG', 'A plant of open, moist, areas along roadsides, ditches, forest margins and thickets. It is an important nectar source for beekeeping late in summer and early fall.', '');
INSERT INTO `main` VALUES (28, 'Fagus grandifolia', 'Beech', 'P', 'eSp,mSp,lSp', 'W', 'NB,NS,ON,PE,QC', '', '', '', 'Foeniculum vulgare_flowers_copyright wiki.user.Philmarin_(CC by-SA 3.0).JPG', 'These spring blooming and handsome woodland trees produce copious amounts of pollen, but not every year.  The pollen is probably not protein rich, but can be eagerly collected by honeybees when no other pollen sources are available.', '');
INSERT INTO `main` VALUES (29, 'Hedysarum boreale', 'Northern hedysarum', 'P,N', 'eSu,mSu,lSu', 'W', 'AB,BC,MB,NF,NT,NS,NU,ON,PE,QC,SK,YT', '', '', '', 'Helianthus annuus_flower_public domain.jpg', '', '');
INSERT INTO `main` VALUES (30, 'Hibiscus trionum', 'Flower-of-an-hour', 'P,N', 'mSu,lSu,eF', 'R', 'MB,NB,NS,ON,PE,QC', '', '', '', 'Hypericum perforatum_flowers_copyright H. Zell_(CC by-SA 3.0).JPG', '', '');
INSERT INTO `main` VALUES (31, 'Lotus corniculatus', 'Bird’s-foot trefoil', 'P,N', 'mSu,lSu', 'W,C', 'AB,BC,MB,NB,NF,NS,ON,PE,QC,SK,YT', '', '', '', 'Lupinus perennis_flowers_public domain.JPG', 'Grown for ground cover, hay and soil improvement.  It is also naturalized on roadsides and waste lands.  Its  value may vary from place to place and year to year.  It can produce nectar for high quality honey.  Honeybees often gather the pollen.', '');
INSERT INTO `main` VALUES (32, 'Lysimachia spp.', 'Garden loosestrife', 'P,N', 'mSu,lSu,eF', 'R', 'AB,BC,MB,NF,NT,NS,ON,PE,QC,SK,YT', '', '', '', 'Lythrum salicaria_flowers_copyright wiki.user.Christian Fischer_(CC by-SA 3.0).jpg', 'There are several species with yellow flowers that grow in various habitats.  The garden loosestrife is attractive to honeybees for nectar and pollen.', '');
INSERT INTO `main` VALUES (33, 'Malus coronaria', 'Crab Apple', 'P,N', 'eSp,mSp,lSp', 'W,C', 'AB,BC,MB,NF,NT,NS,ON,PE,QC,SK,YT', '', '', '', 'Malva moschata_flowers_copyright wiki.user.Albert H_(CC by-SA 3.0).jpg', '', '');
INSERT INTO `main` VALUES (34, 'Monarda fistulosa', 'Horsemint', 'P,N', 'mSu,lSu,eF', 'W', 'AB,BC,MB,NT,ON,QC,SK', '', '', '', '', 'A widespread plant of open areas.  It produced copious nectar that is well collected by honeybees.  The pollen is not easily collected by honeybees because it is retained in the upper “helmet” part of the flowers.  The bergamots are well adapted to pollination by bumblebees.', '');
INSERT INTO `main` VALUES (35, 'Nepeta catarina', 'Catnip', 'P,N', 'mSu,lSu,eF', 'W,R', 'AB,BC,MB,NB,NF,NS,ON,PE,QC,SK', '', '', '', 'Nicotiana tabacum_flowers_copyright wiki.user.Joachim Müllerchen_(CC by 2.5).JPG', 'Primarily a garden plant  but also grows along roadsides and uncultivated areas.  The flowers produce abundant nectar that is gathered extensively by honeybees.  Mints are not known as being important for pollen collection by honeybees.', '');
INSERT INTO `main` VALUES (36, 'Nicotiana tabacum', 'Tobacco', 'P,N', 'lSu,eF', 'C', 'BC,ON,QC', '', '', '', 'Oenothera biennis_flowers_copyright Fritz Geller-Grimm_(CC by-SA 2.5).jpg', 'This crop plant is usually cut before it fully blooms.  Its deep tubular flowers hide the abundant nectar, but honeybees sometimes obtain it through punctures in the base of the flowers made by some kinds of bumblebees.  It is not an important plant for beekeepers.', '');
INSERT INTO `main` VALUES (37, 'Oenothera biennis', 'Evening primrose', 'N', 'mSu,lSu,eF', 'W,R', 'AB,BC,MB,NB,NF,NS,ON,PE,QC,SK', '', '', '', 'Onobrychis viciifolia_flower_public domain.jpg', 'Evening primroses produce large amounts of nectar, but it is not easily accessible for honeybees.  They also produce pollen copiously, but it forms strings on viscin threads.  They are not regarded as iumportant to honeybees or beekeeping.', '');
INSERT INTO `main` VALUES (38, 'Oxalis stricta', 'Wood sorrel', 'P,N', 'eSu,mSu,lSu,eF', 'R', 'AB,BC,MB,NB,NF,NS,ON,PE,QC,SK', '', '', '', 'Paeonia sp._flower_copyright Bob Gutowski_(CC by 2.0).jpg', 'This widespread native plant, often thought of as a weed, is used by honeybees for both its nectar and pollen.', '');
INSERT INTO `main` VALUES (39, 'Paeonia spp.', 'Peony', 'P,N', 'eSu,mSu', 'C', 'AB,BC,MB,NB,NF,NS,ON,PE,QC,SK', '', '', '', 'Papaver sp._flowers, leaves_copyright user.wiki.Jerzy Opiola_(CC by-SA 3.0).jpg', 'Mostly grown as garden plants and as cultivars that produce neither pollen nor nectar.  Native species produce both.  The unopen buds secrete extrafloral nectar that is sometimes collected by honeybees. \r\n', '');
INSERT INTO `main` VALUES (40, 'Prunella vulgaris', 'Self-heal', 'P,N', 'mSu,lSu,eF', 'W,R', 'AB,BC,MB,NB,NF,NS,ON,PE,QC,SK,YT', '', '', '', 'Prunus armeniaca (Apricot)_flowers_copyright wiki.user.Victor Vizu_(CC by-SA 3.0).jpg', 'A plant of garden lawns, roadsides, and open areas it produces nectar and pollen that is taken by honeybees.', '');
INSERT INTO `main` VALUES (41, 'Prunus armeniaca', 'Apricot', 'P,N', 'eSu,mSu,lSu', 'C', 'ON,QC', '', '', '', 'Prunus cerasus (Sour cherry)_flowers_public domain.jpg', 'This early blooming orchard tree (the first of the stone fruits) provides nectar and pollen to honeybees.', '');
INSERT INTO `main` VALUES (42, 'Ribes spp.', 'Currants (various species across Canada)', 'P,N', 'eSu,mSu', 'W,C', 'AB,BC,MB,NF,NT,NS,NU,ON,PE,QC,SK,YT', '', '', '', 'Robinia pseudoacacia_flowers_copyright wiki.user.Mehrajmir13_(CC by-SA 3.0).jpg', 'There are many species of Currants and Gooseberries native to Canada. Their flowers, like those of species grown for fruit, may be well used by honeybees as a source of nectar and pollen.\r\n', '');
INSERT INTO `main` VALUES (43, 'Rudbeckia hirta', 'Cone Flower', 'P,N', 'mSu,lSu,eF,mF', 'W,R', 'AB,BC,MB,NB,NF,NS,ON,PE,QC,SK', '', '', '', '', 'A native plant of open areas that is sometimes well used by honeybees for its nectar.  Its pollen is sometimes collected by honeybees.\r\n', '');
INSERT INTO `main` VALUES (44, 'Symphyotrichum spp.', 'Asters (numerous species)', 'P,N', 'lSu,eF,mF,lF', 'W', 'AB,BC,MB,NB,NF,NS,NU,ON,PE,QC,SK,YT', '', '', '', '', 'Plants in this genus are noted under Aster spp. \r\n', '');
INSERT INTO `main` VALUES (45, 'Alyssum saxatilis', 'Golden cress, Basket of Gold', 'P,N', 'eSu', 'C', 'ON', '', '', '', '', 'A plant of rock gardens. Of minor or little importance to beekeeping.', '');
INSERT INTO `main` VALUES (46, 'Baptisia australis', 'Blue false indigo', 'P,N', 'eSu,mSu,lSu', 'C', 'ON', '', '', '', '', 'Naturally, this plant grows in rich woods and thickets with plenty of light.  It is also grown ornamentally. \r\n', '');
INSERT INTO `main` VALUES (47, 'Baptisia tinctoria', 'Yellow indigo', 'P,N', '', 'W', '', '', '', '', '', 'Grwos in open areas and can be a good nectar source for honeybees.', '');
INSERT INTO `main` VALUES (48, 'Castanea dentata', 'Chestnut', 'P,r', 'eSu', 'W', 'ON', '', '', '', '', 'These rare, early blooming woodland trees (chestnut) produce  large amounts of pollen in early spring.  The pollen is probably not protein rich, but can be eagerly collected by honeybees when no other pollen sources are available.  \r\n', '');
INSERT INTO `main` VALUES (49, 'Cladrastis kentukea', 'Yellow Wood', 'P,N', 'eSu,mSu', 'C', 'ON', '', '', '', '', '', '');
INSERT INTO `main` VALUES (50, 'Cucumis melo', 'Melon', 'P,N', 'mSu', 'C', 'ON', '', '', '', '', 'Field grown melons produce nectar in abundance and it is well gathered by honeybees.  The pollen, produced only on male flowers, is spiny and sometimes ignored or groomed off by foraging honeybees.\r\n', '');
INSERT INTO `main` VALUES (51, 'Gleditsia triacanthos', 'Honey Locust', 'P,N', 'eSu,mSu', 'W,C', 'ON', '', '', '', '', 'Despite its name and production of larger amounts of nectar, the bloom period tends to be short.  Honeybees forage from flowers on the trees and those that have fallen. It is native to southern Ontario, but thorn-less cultivars are widely planted along city streets and on roadsides.\r\n', '');
INSERT INTO `main` VALUES (52, 'Lespedeza bicolor', 'Shrub bush-clover', 'P,N', 'mSu,lSu', 'C', 'ON', '', '', '', '', 'This is a very attractive nectar plant for bees where it occurs in southern Ontario.\r\n', '');
INSERT INTO `main` VALUES (53, 'Liatris cylindracea', 'Blazing star', 'P,N', 'mSu,lSu,eF', 'W', 'ON', '', '', '', '', 'This is an attractive nectar and pollen plant for honyebees where it occurs in southern Ontario.\r\n', '');
INSERT INTO `main` VALUES (54, 'Linum flavum', 'Yellow flax', 'P', 'mSu', 'C', 'ON', '', '', '', '', 'Mostly grown ornamentally.  Honeybees sometimes collect pollen from the flowers.\r\n', '');
INSERT INTO `main` VALUES (55, 'Linum perenne', 'Perennial flax', 'P,N', 'mSu,lSu,eF', 'R', 'ON', '', '', '', '', 'Mostly grown ornamentally.  Honeybees sometimes collect pollen and nectar from the flowers.\r\n', '');
INSERT INTO `main` VALUES (56, 'Liriodendron tulipifera', 'Tulip Tree', 'P,N', 'eSp,mSp,lSp,eSu', 'W', 'ON', '', '', '', '', 'The large flowers produce an overabundance of nectar in early to mid-summer.  It is a native tree, one of the tallest, of the Carolinian forest of Ontario but highly localized.  It is grown as an ornamental tree.  Although the flowers produce lots of pollen, it seems little gathered by honeybees.  \r\n', '');
INSERT INTO `main` VALUES (57, 'Ptelea trifoliata', 'Hop tree', 'P,N', 'mSu', 'W', 'ON', '', '', '', '', 'This native treelet is dioecious.  Flowers on male plants produce pollen and nectar, and those on female plants only nectar.  They may literally buzz with honeybees, and other flower-visiting insects. Although it can be grown as an ornamental outside its natural Canadian range which is along the shore of Lake Erie\r\n', '');
INSERT INTO `main` VALUES (58, 'Vigna sinensis', 'Cow pea', 'P,N', 'mSu', 'C', 'ON', '', '', '', '', '', '');
INSERT INTO `main` VALUES (59, 'Agostache foeniculum', '', '', '', '', '', '', '', '', '', 'Sometimes grown as an ornamental.  It is highly attractive to honeybees as an excellent source of nectar. \r\n', '');
INSERT INTO `main` VALUES (60, 'Ailanthus altissima', '', '', '', '', '', '', '', '', '', 'An adventive treelet that grows in southern Ontario.  Its flowers produce abundant nectar that is gathered by honeybees.  The honey is reputed to have an unpleasant flavor that improves with curing. \r\n', '');
INSERT INTO `main` VALUES (61, 'Allium spp. ', '', '', '', '', '', '', '', '', '', 'Most onions and chives produce nectar in moderate amounts, but honeybees seem not to use them extensively.  Wild onions, A. cernuum (Nodding Onion of open woodlands) and A. stellatum (Autumn Onion of rocky slopes, prairies \r\n', '');
INSERT INTO `main` VALUES (62, 'Amorpha canescens', '', '', '', '', '', '', '', '', '', 'This dwarf shrub, mostly of prairie sites is used by honeybees for nectar and pollen. Its long blooming period makes it valuable to beekeeping where it grows abundantly.\r\n', '');
INSERT INTO `main` VALUES (63, 'Anenome patens (Pulustilla patens)', '', '', '', '', '', '', '', '', '', 'Grows in open grassy areas.  A good source of pollen early in the year.\r\n', '');
INSERT INTO `main` VALUES (64, 'Apocynum androsaemifolium', '', '', '', '', '', '', '', '', '', 'This bushy plant occurs along the edges of woodlots, thickets, fencelines, and in fields.  It is used by honeybees as a source of nectar\r\n', '');
INSERT INTO `main` VALUES (65, 'Aralia spinosa', '', '', '', '', '', '', '', '', '', 'A treelet that produces large flat umbels of flowers that produce nectar copiously and may hum with honeybee activity.  Locally planted as an ornamental so not important in beekeeping. \r\n', '');
INSERT INTO `main` VALUES (66, 'Arbutus menziesii', '', '', '', '', '', '', '', '', '', 'This small tree, native to BC, is an excellent source of nectar that makes a valued honey.  It is not visited by honeybees for its pollen.\r\n', '');
INSERT INTO `main` VALUES (67, 'Asclepias incarnata', '', '', '', '', '', '', '', '', '', 'Swamp milkweed is an excellent source of nectar sometimes used locally  by honeybees. Its pollen is produced in packets (pollinia) and is not collected by honeybees, except accidentally as the pollinia attach to the bees’ legs.\r\n', '');
INSERT INTO `main` VALUES (68, 'Asclepias tubersoa', '', '', '', '', '', '', '', '', '', 'Butterfly weed is becoming popular for attracting flower visiting insect to gardens.  Its bright orange flowers produced in large heads are well used by bees as a source of nectar, but it is not known as an plant important to honeybees. Its pollen is produced in packets (pollinia) and is not collected by bees, except \r\n', '');
INSERT INTO `main` VALUES (69, 'Aster (a.k.a. Symphotrichium)', '', '', '', '', '', '', '', '', '', 'Perennial plants of open areas, they are important sources of nectar and sometimes pollen, especially in the Fall.  There are several native species, and a range of horticultural asters.\r\n', '');
INSERT INTO `main` VALUES (70, 'Bidens spp.', '', '', '', '', '', '', '', '', '', 'The tickseeds are similar in being weedy and  producing yellow daisy-like heads.  They produce seeds with barbed spines that hook onto animal fur and human clothing.  The flowers are well used by honeybees for both nectar and pollen.  Coreopsis is similar.\r\n', '');
INSERT INTO `main` VALUES (71, 'Campanula spp.', '', '', '', '', '', '', '', '', '', 'Bell flowers are usually blue, but may white cultivars and variants are known.  They can be highly attractive to bees as a source of nectar and pollen.  They are popular garden plants. Native species can be abundant in meadows, rocky banks and slopes. \r\n', '');
INSERT INTO `main` VALUES (72, 'Catalpa bignonioides', '', '', '', '', '', '', '', '', '', 'This widely grown ornamental tree produces nectar in its flowers and from extra-floral nectaries.  Honeybees take nectar from the flowers by crawling into them.  \r\n', '');
INSERT INTO `main` VALUES (73, 'Ceanothus americanus', '', '', '', '', '', '', '', '', '', 'A shrub of open areas, its white flowers produce both nectar and pollen that is taken by honeybees.\r\n', '');
INSERT INTO `main` VALUES (74, 'Celastruss candens', '', '', '', '', '', '', '', '', '', 'Honeybees work the small scentless blossoms well for nectar.  This is a vine of rich woodlands and forest margins.\r\n', '');
INSERT INTO `main` VALUES (75, 'Cladastris lutea', '', '', '', '', '', '', '', '', '', 'Native to SE USA, it is grown as an ornamental in Ontario and Quebec.  Its flowers produce large amounts of nectar that is eagerly collected by honeybees and bumblebees.  \r\n', '');
INSERT INTO `main` VALUES (76, 'Cleome spp. ', '', '', '', '', '', '', '', '', '', 'The spider flowers, and especially bee plant, are highly valued for the prolific crops of nectar they can produce, especially in the west.  They are also valuable for their pollen, which honeybees gather from the bee plant while hovering.  Some spider plants are important sources of nutrition for other species of bee. \r\n', '');
INSERT INTO `main` VALUES (77, 'Cucurbita maxima', '', '', '', '', '', '', '', '', '', 'Squash and pumpkins produce nectar in huge abundance and it is well gathered by honeybees.  The pollen, produced only on male flowers, is spiny and generally ignored or groomed off by foraging honeybees. The native, hoary squash bee, uses the pollen of squash and pumpkin plants as the sole source for feed for its young.\r\n', '');
INSERT INTO `main` VALUES (78, 'Cucurbita moschata', '', '', '', '', '', '', '', '', '', 'Squash and pumpkins produce nectar in huge abundance and it is well gathered by honeybees.  The pollen, produced only on male flowers, is spiny and generally ignored or groomed off by foraging honeybees. The native, hoary squash bee, uses the pollen of squash and pumpkin plants as the sole source for feed for its young.\r\n', '');
INSERT INTO `main` VALUES (79, 'Cucurbita pepo', '', '', '', '', '', '', '', '', '', 'Squash and pumpkins produce nectar in huge abundance and it is well gathered by honeybees.  The pollen, produced only on male flowers, is spiny and generally ignored or groomed off by foraging honeybees. The native, hoary squash bee, uses the pollen of squash and pumpkin plants  as the sole source for feed for its young. \r\n', '');
INSERT INTO `main` VALUES (80, 'Coreopsis lanceolata', '', '', '', '', '', '', '', '', '', 'These plants are sometimes called tickseeds because they are similar to Bidens in general appearance.  They are showier and grown in gardens, but also grow as in open disturbed places.  The yellow-flowered daisy-like heads are well used by honeybees for both nectar and pollen.\r\n', '');
INSERT INTO `main` VALUES (81, 'Coreopsis spp.', '', '', '', '', '', '', '', '', '', 'There are several species that grow in open, uncultivated areas. Mostly as weeds.  They are an especially good source of pollen for honeybees. \r\n', '');
INSERT INTO `main` VALUES (82, 'Coreopsis tinctoria', '', '', '', '', '', '', '', '', '', 'These plants are sometimes called tickseeds because they are similar to Bidens in general appearance.  They are showier and grown in gardens, but also grow as in open disturbed places.  The yellow-flowered daisy-like heads are well used by honeybees for both nectar and pollen.\r\n', '');
INSERT INTO `main` VALUES (83, 'Epilobium (now Chamerion) angustifolium', '', '', '', '', '', '', '', '', '', 'Grows well in open areas, especially after forest fires.  It can form huge stands with flowers that secrete large amounts of nectar.  Honeybees tend not to collect its pollen with its grains held together loosely with strands of viscin. \r\n', '');
INSERT INTO `main` VALUES (84, 'Erigeron speciosus', '', '', '', '', '', '', '', '', '', 'Native to open areas and as a garden plant, it is well visited by honeybees for its nectar.\r\n', '');
INSERT INTO `main` VALUES (85, 'Fragaria × ananassa', '', '', '', '', '', '', '', '', '', 'Cultivated strawberry blossoms are used by honeybees as sources of nectar and pollen.\r\n', '');
INSERT INTO `main` VALUES (86, 'Gentianopsis crinata', '', '', '', '', '', '', '', '', '', 'The fringed gentian grows in open moist areas and may be a minor source of nectar and pollen for honeybees late in summer.\r\n', '');
INSERT INTO `main` VALUES (87, 'Grossularia( or Ribes) uva-crispa', '', '', '', '', '', '', '', '', '', 'Gooseberries are grown for their fruit.  The flowers are well used by honeybees  as a source of nectar and pollen.\r\n', '');
INSERT INTO `main` VALUES (88, 'Helenium autumnale', '', '', '', '', '', '', '', '', '', 'A weedy plant that grows in thickets and meadows, in vacant land and sometimes in uncultivated fields. A good source of nectar and pollen.\r\n', '');
INSERT INTO `main` VALUES (89, 'Hyssopus officinalis', '', '', '', '', '', '', '', '', '', 'This introduced herd has escaped into roadsides and dry pastures in some places.  It is higly attractive to honeybees that forage for its floral nectar.\r\n', '');
INSERT INTO `main` VALUES (90, 'Larix spp.', '', '', '', '', '', '', '', '', '', 'Tamaracks may be used by honeybees if honeydew is being produced by sap-sucking insects (e.g. aphids, scale insects).  Honeybees use may use tamarack gum for propolis.\r\n', '');
INSERT INTO `main` VALUES (91, 'Lavandula angustifolia (a.k.aL. officinalis)', '', '', '', '', '', '', '', '', '', 'Honeybees forage extensively for nectar. The resulting honey is characteristically scented and flavoured.  This plant is being increasingly cultivated in southern Ontario.\r\n', '');
INSERT INTO `main` VALUES (92, 'Liatris spicata', '', '', '', '', '', '', '', '', '', 'A native to moist prairies, it is grown extensively ornamentally .It  is an attractive nectar and pollen plant for honeybees.\r\n', '');
INSERT INTO `main` VALUES (93, 'Malus(sometimes Pyrus) coronaria', '', '', '', '', '', '', '', '', '', 'This native crab apple is native in southern Ontario.  It grows in moist areas and bottom lands where it can be a valuable source of nectar and pollen to honeybees.  Various horticultural cultivars have been bred and selected, those with double flowers are not useful to bees. \r\n', '');
INSERT INTO `main` VALUES (94, 'Mentha spp.', '', '', '', '', '', '', '', '', '', 'All the mints grow in open areas and gardens.  The flowers produce abundant nectar that is gathered extensively by honeybees.  Mints are not known as being important for pollen collection by honeybees. \r\n', '');
INSERT INTO `main` VALUES (95, 'Myrica gale', '', '', '', '', '', '', '', '', '', 'This shrub of wet, acid areas produces catkins that can be important to honeybees for the abundant pollen produced early in the spring. \r\n', '');
INSERT INTO `main` VALUES (96, 'Prunus americana', '', '', '', '', '', '', '', '', '', 'An early flowering native tree of southern Ontario and Manitoba.  It is planted beyond its range for flowering and fruits.  Locally,  itprovides nectar and pollen to honeybees.\r\n', '');
INSERT INTO `main` VALUES (97, 'Carthamnus tinctorius', 'Safflower', '', '', '', '', '', '', '', '', 'Where this plant is grown as a crop,  it can be a valuable source of nectar.  Honeybees tend to work the flowering heads in the morning.\r\n', '');
INSERT INTO `main` VALUES (98, 'Chierathus spp. ', 'Wall -flower', '', '', '', '', '', '', '', '', 'These plants are popular garden flowers are worked by honeybees and many other flower visitors for their abundant nectar and pollen.\r\n', '');
INSERT INTO `main` VALUES (99, 'Hydrophyllum virginianum', 'Waterleaf', '', '', '', '', '', '', '', '', 'Grows in damp places and can be a valuable source of nectar for honeybees. \r\n', '');
INSERT INTO `main` VALUES (100, 'Limnanthes douglasii', 'Meadow foam', '', '', '', '', '', '', '', '', 'This plant is highly attractive for its nectar.  It can produce a carpet of bloom in sunny areas when in garden cultivation\r\n', '');
INSERT INTO `main` VALUES (101, 'Linum usitatissimum', 'Flax, Linseed', '', '', '', '', '', '', '', '', 'This crop plant has flowers that bloom for one day in the morning.  It is generally not regarded as highly valuable for beekeeping.  The flowers produce small amounts of nectar and little pollen (which is pale blue)\r\n', '');
INSERT INTO `main` VALUES (102, 'Parthenocissus quinquefolia', 'Virginia creeper', '', '', '', '', '', '', '', '', 'Climbing vine plant of forest margins, thickets, fence lines and gardens.  The inconspicuous flowers produce much nectar that is well collected by honeybees.    \r\n', '');
INSERT INTO `main` VALUES (103, 'Penstemon spp.', 'Beard-tongues', '', '', '', '', '', '', '', '', 'The beardtongues are of minor importance as nectar sources for honeybees. They are attended well by bumblebees.\r\n', '');
INSERT INTO `main` VALUES (104, 'Phacelia tanacetifolia', 'Lacy phacelia', '', '', '', '', '', '', '', '', 'Recommended as a cover and companion crop, this plant produces abundant nectar and pollen well suede by honeybees.  It has been recommended to be planted for honeybee forage. \r\n', '');
INSERT INTO `main` VALUES (105, 'Pinus spp. ', 'Pines', '', '', '', '', '', '', '', '', 'The pines may be used by honeybees if honeydew is being produced by sap-sucking insects (e.g. aphids, scale insects).  Honeybees use pine gum for propolis.\r\n', '');
INSERT INTO `main` VALUES (106, 'Polygonum spp.', 'Knotweeds', '', '', '', '', '', '', '', '', 'Several species of knotweeds grow in Canada.  Black bindweed (P. convolvulus) and Japanese knotweed (P. cuspidatum) are fast spreading weeds). They are useful  nectar sources for honeybees and beekeeping\r\n', '');
INSERT INTO `main` VALUES (107, 'Prunus avium', 'Sweet cherry', '', '', '', '', '', '', '', '', 'This early blooming orchard tree provides nectar and pollen to honeybees.\r\n', '');
INSERT INTO `main` VALUES (108, 'Prunus nigra', 'Canadian plum', '', '', '', '', '', '', '', '', 'Small trees, often in clumps.  Flowers early and may be used by honeybees as sources of nectar and pollen.\r\n', '');
INSERT INTO `main` VALUES (109, 'Prunus spinosa', 'Blackthron', '', '', '', '', '', '', '', '', 'A cultivated early flowering tree in southern Ontario.  It is visited by honeybees for nectar and pollen.\r\n', '');
INSERT INTO `main` VALUES (110, 'Prunus virginiana', 'Chokecherry', '', '', '', '', '', '', '', '', 'This native tree of woodland edges, thickets, and hedges blooms prolifically and attracts many insects to its flowers.  It can be well visited by honeybees for nectar and pollen, but it is not considered to be of major importance. \r\n', '');
INSERT INTO `main` VALUES (111, 'Pyrus arbutifolia', 'Red chokeberry', '', '', '', '', '', '', '', '', 'This spreading shrub of open areas and abandoned farmlands can be a locally important source of nectar and pollen to honeybees. \r\n', '');
INSERT INTO `main` VALUES (112, 'Pyrusmalus (also Malusdomestica)', 'European crab apple', '', '', '', '', '', '', '', '', 'Apple blossoms are well known as being eagerly worked by honeybees for nectar and pollen.\r\n', '');
INSERT INTO `main` VALUES (113, 'Rhododendron (Azalea)', 'Rhodendron', '', '', '', '', '', '', '', '', 'There are several species and many cultivars of these shrubs used horticulturally.  Those, and the wild species are not known as important for honeybees, but some are well used by bumblebees as sources of nectar and pollen. \r\n', '');
INSERT INTO `main` VALUES (114, 'Rhus typhina', 'Staghorn sumac', '', '', '', '', '', '', '', '', 'The clonal thickets of this dioecioustreelet of roadsides and open areas are an important source of nectar (from flowers of both sexes of plant) and pollen from the male plants.  Honeybees tend to visit flowers of male plants in the morning when the pollen is liberated and female plants in the afternoon\r\n', '');
INSERT INTO `main` VALUES (115, 'Ribes nigrum', 'Black currant', '', '', '', '', '', '', '', '', 'Black currants are grown for their fruit.  The flowers are well used by honeybees as a source of nectar and pollen.\r\n', '');
INSERT INTO `main` VALUES (116, 'Ribes rubrum', 'Red currant', '', '', '', '', '', '', '', '', 'Red currants are grown for their fruit.  The flowers are well used by honeybees as a source of nectar and pollen.\r\n', '');
INSERT INTO `main` VALUES (117, 'Solidago spp. ', 'Goldenrods', '', '', '', '', '', '', '', '', 'The goldenrods are very diverse in species and habits.  S. canadensisis a well-known perennial and weedy plant of open areas that can form huge stands of large clones.  The flowers are important source of nectar, especially in the Fall.  \r\n', '');
INSERT INTO `main` VALUES (118, 'Sonchus arvensis', 'Field snowthistle', '', '', '', '', '', '', '', '', 'Weedy plants of roadsides and waste land.  Not considered important to honeybees for either nectar or pollen, though they are known to visit the flowering heads to forage.\r\n', '');
INSERT INTO `main` VALUES (119, 'Sorbus (sometimes Pyrus) aucuparia', 'European mountain ash', '', '', '', '', '', '', '', '', 'A commonly grown small tree.  It flowers profusely and produces abundant nectar and pollen.  They are not well worked by honeybees if other more attractive flowers are in bloom.  The flowers have a characteristic “off” scent.   \r\n', '');
INSERT INTO `main` VALUES (120, 'Stachys lanata(a.k.a. S. byzantina)', 'Wooly hedgenettle', '', '', '', '', '', '', '', '', 'Much grown as an ornamental and garden cover plant.  Its flowers produce much nectar which is gathered by honeybees.  It is commonly visited by the introduced carder bee that uses the leave’s wooly hairs in nest building. \r\n', '');
INSERT INTO `main` VALUES (121, 'Symphoricarpus albus', 'Common snowberry', '', '', '', '', '', '', '', '', 'This widespread shrub of open and rocky areas is considered an important plant for honeybees and beekeeping for both nectar and pollen. \r\n', '');
INSERT INTO `main` VALUES (122, 'Symporicarpus occidentalis', 'Western snowberry', '', '', '', '', '', '', '', '', 'A shrub of dry prairies and plains.  It is used by honeybees for both nectar and pollen.\r\n', '');
INSERT INTO `main` VALUES (123, 'Taxus spp.', 'Yews', '', '', '', '', '', '', '', '', 'The yews may be used by honeybees for the abundant, light weight, pollen they produce.\r\n', '');
INSERT INTO `main` VALUES (124, 'Vaccinium angustifolium', 'Lowbush blueberry', '', '', '', '', '', '', '', '', 'Although an important plant for lowbush blueberry production, it does not produce much nectar and its pollen is mostly ignored by honeybees.  Honeybee colonies s placed on lowbush blueberry grounds for pollination often weaken. \r\n', '');
INSERT INTO `main` VALUES (125, 'Vaccinium corymbosum', 'Highbush blueberry', '', '', '', '', '', '', '', '', 'Highbush blueberries produce floral nectar abundantly.  Some are more attractive to honeybees than others,  in some cases because the shape of the flowers prevents them from obtaining the hidden nectar. \r\n', '');
INSERT INTO `main` VALUES (126, 'Vaccinium macrocarpon', 'Cranberry', '', '', '', '', '', '', '', '', 'Cranberry flower produce little nectar, but honeybees sometimes collect pollen.  The flowers are best pollinated by bumblebees\r\n', '');
INSERT INTO `main` VALUES (127, 'Vaccinium vitis-idea', 'Lingonberry', '', '', '', '', '', '', '', '', 'This creeping cranberry of northern rocky and peaty areas may produce a little nectar and pollen of use to honeybees locally.\r\n', '');
INSERT INTO `main` VALUES (128, 'Verbena hastata', 'Swamp verbena', '', '', '', '', '', '', '', '', 'A plant of open areas and roadsides, it is sometimes well attended by honeybees foraging for nectar and pollen, although it produces only small amounts.  There are various horticultural selections of vervains that are used to encourage butterflies to gardens.  \r\n', '');
INSERT INTO `main` VALUES (129, 'Verbesina alternifolia', 'Wingstem', '', '', '', '', '', '', '', '', 'This plant, bets grown on rich soils, has a reputation for producing abundant nectar that is taken by honeybees.  It grows in southern Ontario in thickets and forest borders.\r\n', '');
INSERT INTO `main` VALUES (130, 'Viburnum spp.', 'Viburnums', '', '', '', '', '', '', '', '', 'There are several native species as well as horticultural selections and species.  Honeybees use some for their floral nectar and pollen. \r\n', '');
INSERT INTO `main` VALUES (131, 'Acer rubrum', 'Red Maple', 'P,N', 'eSp,mSp,lSp,eSu', 'W', 'NB,NF,NS,ON,PE,QC', '', '', '', '', 'This early blooming tree of wet areas produces large amounts of nectar.  Trees may literally buzz with honeybees working the tufts of reddish flowers.  It is not known by beekeepers for pollen production.\r\n', '');
INSERT INTO `main` VALUES (132, 'Allium cernuum', 'Nodding Onion', 'P,N', 'mSu', 'W', 'AB,BC,ON,SK', '', '', '', '', '', '');
INSERT INTO `main` VALUES (133, 'Amelanchier alnifolia', 'Serviceberry, Saskatoon', 'P,N', 'lSp,eSu', 'W', 'AB,MB,NT,NS,ON,PE,QC,SK,YT', '', '', '', '', 'This small tree of woodland margins, thickets, stream banks and regenerating old-fieldsproduces masses of white flowers early in summer.  The flowers are well visited by honeybees for both nectar and pollen.  The value of this plant to beekeeping is not well recognized.\r\n', '');
INSERT INTO `main` VALUES (135, 'Amorpha fruticosa', 'False indigo', 'P,N', 'eSu', 'W', 'MB,NB,ON,QC', '', '', '', '', 'This shrub can form quite dense thickets along stream and riverbanks and is used by honeybees for nectar and pollen after fruit trees have bloomed and before clovers have started.\r\n', '');
INSERT INTO `main` VALUES (136, 'Betula spp.', 'Birch ', 'P', 'eSp,mSp,lSp', 'W', 'AB,BC,MB,NF,NS,ON,PE,QC,SK,YT', '', '', '', '', 'These early blooming woodland trees produce copious amounts of pollen in early spring.  The pollen is probably not protein rich, but can be eagerly collected by honeybees when no other pollen sources are available.  \r\n', '');
INSERT INTO `main` VALUES (137, 'Sanguinaria canadensis', 'Bloodroot', 'P', 'eSp,mSp,lSp', 'W', 'MB,NB,NS,ON,QC', '', '', '', '', 'Very early blooming plant of woodlands. Can be useful to honeybees for pollen early in spring\r\n', '');
INSERT INTO `main` VALUES (138, 'Ulmus americana', 'Elm', 'P', 'eSp,mSp,lSp', 'W', 'MB,NB,NS,ON,PE,QC,SK', '', '', '', '', 'These spring blooming and handsome trees produce copious amounts of pollen.  The pollen is eagerly sought by honeybees and has high protein content relative to other spring-blooming and wind pollinated trees. \r\n', '');
INSERT INTO `main` VALUES (139, 'Picea spp.', 'Spruces', 'Hd,r', 'eSu', 'R', 'AB,BC,MB,NF,NT,NS,NU,ON,PE,QC,SK,YT', '', '', '', '', 'The spruces may be used by honeybees if honeydew is being produced by sap-sucking insects (e.g. aphids, scale insects).  Honeybees use spruce gum for propolis.\r\n', '');
INSERT INTO `main` VALUES (140, 'Angelica atropurpurea', 'Angelica', 'P,N', 'mSu', 'W', 'NB,NF,NS,ON,PE,QC', '', '', '', '', 'Grows widely in bottom lands, swamps and rich woodlands.  It is not noted as important to honeybees or beekeeping, but may be important locally.\r\n', '');
INSERT INTO `main` VALUES (141, 'Aralia spp.', 'Sarsaparilla', 'P,N', 'mSu,lSu', 'W', 'AB,BC,MB,NF,NT,NS,ON,PE,QC,SK,YT', '', '', '', '', 'Wild sarsaparillas are woodland plants, not considered important for honeybees and beekeeing, but well used by some species of bumblebees\r\n', '');
INSERT INTO `main` VALUES (142, 'Berberis spp. ', 'Barberry', 'P,N', 'eSu,mSu', 'W', 'BC,MB,NB,NS,ON,PE,QC,SK', '', '', '', '', 'These bushes, when abundant, are well visited by honeybees for nectar and pollen. \r\n', '');
INSERT INTO `main` VALUES (143, 'Cephalanthus occidentalis', 'Button Bush', 'P,N', 'mSu,lSu', 'W', 'NB,NS,ON,PE,QC', '', '', '', '', 'Native to eastern Canada, this shrub grows in wet, open areas. I can be a valued plant for beekeeping, notably for its nectar, because when it blooms few other flowers are available.  \r\n', '');
INSERT INTO `main` VALUES (144, 'Claytonia virginica', 'Spring Beauty', 'P,N', 'eSu', 'W', 'ON,QC', '', '', '', '', 'Very early blooming plant of woodlands. Can be useful to honeybees early in spring\r\n', '');
INSERT INTO `main` VALUES (145, 'Cornus stolonifera', 'Red osier dogwood', 'P,N', 'mSu', 'W', 'AB,BC,MB,NF,NT,NS,NU,ON,PE,QC,SK,YT', '', '', '', '', 'The red osier dogwood is a prolific producer of nectar and pollen used by many insects and well used by honeybees, especially as a early summer nectar source.\r\n', '');
INSERT INTO `main` VALUES (146, 'Cornus sericea', 'Red osier dogwood', 'P,N', 'mSu', 'W', 'AB,BC,MB,NF,NT,NS,NU,ON,PE,QC,SK,YT', '', '', '', '', '', '');
INSERT INTO `main` VALUES (147, 'Crataegus spp.', 'Hawthorn', 'P,N', 'lSp,eSu', 'W', 'AB,BC,MB,NB,NF,NS,ON,PE,QC,SK', '', '', '', '', 'These small trees and bushes of woodland margins, thickets, stream banks and regenerating old-fields produce masses of white flowers early in summer.  The flowers are well visited by honeybees for both nectar and pollen.  The value of this plant to beekeeping is not well recognized.\r\n', '');
INSERT INTO `main` VALUES (148, 'Diervilla lonicera', 'Bush Honeysuckle', 'P,N', 'mSu', 'W', 'MB,NB,NF,NS,ON,PE,QC,SK', '', '', '', '', 'This shrub can be a valuable early source of nectar for honeybees.  The floral tubes are too long to allow then to extract all the nectar.  It is well used \r\n', '');
INSERT INTO `main` VALUES (149, 'Gaylussacia baccata', 'Black Huckleberry', 'P,N', 'eSu', 'W', 'NB,NF,NS,ON,PE,QC', '', '', '', '', 'Huckleberries should be regarded as important nectar plants for honeybees in localized situations, but are not as well known for their value in beekeeping as other members of the heath family (Ericaceae).\r\n', '');
INSERT INTO `main` VALUES (150, 'Gentianella crinata', 'Fringed gentian', 'P,N', 'lSu,eF,mF', 'W', 'MB,ON,QC', '', '', '', '', 'The fringed gentian grows in open moist areas and may be a minor source of nectar and pollen for honeybees late in summer.\r\n', '');
INSERT INTO `main` VALUES (151, 'Geranium bicknelli', 'Bicknell''s Cranesbill, Northern Cranesbill', 'P,N', 'mSu', 'W', 'AB,BC,MB,NF,NT,NS,ON,PE,QC,SK,YT', '', '', '', '', 'The wild and naturalized geraniums produce nectar and pollen, but their values to honeybees are not well recorded.\r\n', '');
INSERT INTO `main` VALUES (152, 'Hamamelis virginiana', 'Witchhazel', 'P,N', 'mF,lF', 'W', 'NB,NS,ON,PE,QC', '', '', '', '', 'The flowers of this shrub are attractive to honeybees for both nectar and pollen. It grows in dry to moist woodlands and thickets in parts of eastern Canada.\r\n', '');
INSERT INTO `main` VALUES (153, 'Kalmia angustifolia', 'Sheep laurel', 'P,N', 'mSu', 'W', 'NB,NF,NS,ON,PE,QC', '', '', '', '', 'Sheep laurel is not well used by honeybees for either its nectar or pollen. It can be an important source of nectar  in special locations, such as heathlands in the Maritime pronvinces.  Its nectar is poisonous.\r\n', '');
INSERT INTO `main` VALUES (154, 'Rhododendron groenlandicum', 'Labrador tea', 'P,N', 'eSu,mSu', 'W', 'AB,BC,MB,NF,NT,NS,NU,ON,PE,QC,SK,YT', '', '', '', '', '', '');
INSERT INTO `main` VALUES (155, 'Penstemon gracilis', 'Slender penstemon', 'P,N', 'mSu', 'W', 'AB,BC,MB,ON,SK', '', '', '', '', '', '');
INSERT INTO `main` VALUES (156, 'Phacelia linearis ', 'Scorpion weed, Phacelia', 'P,N', 'eSp,mSp,lSp,eSu,mSu,lSu,eF,mF,lF', 'W', 'AB,BC', '', '', '', '', '', '');
INSERT INTO `main` VALUES (157, 'Phryma leptostacha', 'Lop seed', 'P,N', 'mSu,lSu,eF', 'W', 'MB,NB,ON,QC', '', '', '', '', 'A wild plant of minor importance to honeybees for nectar, and possibly for pollen.\r\n', '');
INSERT INTO `main` VALUES (158, 'Polygonum hydropiperoides and other spp.', 'Smartweed', 'P,N', 'mSu', 'W', 'MB,NB,NF,NS,ON,PE,QC,SK', '', '', '', '', 'This is a super special plant\r\n', '');
INSERT INTO `main` VALUES (159, 'Prunus pensylvanica', 'Pin Cherry', 'P,N', 'eSp', 'W', 'AB,BC,MB,NF,NT,NS,ON,PE,QC,SK,YT', '', '', '', '', 'This native tree of woodland edges, thickets, and hedges blooms prolifically and attracts many insects to its flowers.  It can be well visited by honeybees for nectar and pollen, but it is not considered to be of major importance.\r\n', '');
INSERT INTO `main` VALUES (160, 'Rhamnus cathartica', 'Buckthorn', 'P,N', 'mSu', 'W', 'AB,MB,NB,NF,NS,ON,PE,QC,SK', '', '', '', '', 'This invasive shrub is dioecious.  Flowers on male plants produce pollen and nectar, and those on female plants only nectar.  It forms thickets along fence-lines and woodland margins. \r\n', '');
INSERT INTO `main` VALUES (161, 'Rhododendron canadense', 'Rhodora', 'P,N', 'lSp', 'W', 'AB,NB,NF,NS,ON,PE,QC', '', '', '', '', '', '');
INSERT INTO `main` VALUES (162, 'Stachys spp.', 'Hedgenettle', 'P,N', 'lSp', 'W', 'AB,BC,MB,NF,NT,NS,ON,PE,QC,SK,YT', '', '', '', '', '', '');
INSERT INTO `main` VALUES (163, 'Tilia americana', 'Basswood', 'P,N', 'mSu', 'W', 'MB,NB,NS,ON,PE,QC', '', '', '', '', 'The basswoods or lindens are well appreciated by beekeepers for their value as nectar plants. The trees tend to bloom every second year.  The flowers are not foraged much for pollen. \r\n', '');
INSERT INTO `main` VALUES (164, 'Verbena spp.', 'Vervain', 'P,N', 'mSu,lSu,eF,mF', 'W', 'AB,BC,MB,NB,NS,ON,QC,SK', '', '', '', '', '', '');
INSERT INTO `main` VALUES (165, 'Viburnum lentago', 'Nanny berry', 'P,N', 'eSu,mSu', 'W', 'MB,NB,ON,QC,SK', '', '', '', '', 'This large shrub or treelet flowers prolifically.  Many insects, including honeybees forage at the flowers for nectar and pollen. \r\n', '');
INSERT INTO `main` VALUES (166, 'Vicia americana', 'American Vetch', 'P,N', 'eSu,mSu', 'W', 'AB,BC,MB,NT,ON,QC,SK,YT', '', '', '', '', 'Widespread, sometimes forming tangled masses of vegetation in open areas.  It is not considered to be an important nectar or pollen plant for honeybees. \r\n', '');
INSERT INTO `main` VALUES (167, 'Zanthoxylum americanum', 'Prickly Ash', 'P,N', 'eSp,mSp', 'W', 'ON,QC', '', '', '', '', 'This dioecious shrub or treelet grows in open areas, especially with limestone substrate.  Its small flowers produce nectar which is gathered by honeybees and many other insects.\r\n', '');
INSERT INTO `main` VALUES (168, 'Rhamnus alnifolia', 'Alder leaved Buckthorn', 'P,N', 'mSu', 'W,A', 'AB,BC,MB,NB,NF,NS,ON,PE,QC,SK', '', '', '', '', '', '');
INSERT INTO `main` VALUES (169, 'Scrophularia marilandica', 'Carpenter''s square', 'N', 'mSu', '', 'ON,QC', '', '', '', '', 'A native plant of rich woods and thicket without too much shade. The flowers are an excellent source of nectar for honeybees. \r\n', '');
INSERT INTO `main` VALUES (170, 'Anthyllis vulneraria', 'Kidney vetch', 'P,N', 'mSu', '', 'MB,NB,NF,ON,QC', '', '', '', '', 'Grows locally in fields and wastelands.  It is used by honeybees locally as a source of pollen and nectar, but not regarded as important to beekeeping\r\n', '');
INSERT INTO `main` VALUES (171, 'Brassica spp.', 'Canola, Mustards', 'P,N', 'eSu,mSu,lSu', 'C,R', 'AB,BC,MB,NB,NF,NS,ON,PE,QC,SK', '', '', '', '', 'Canola and mustard are grown extensively as crop plants for their oily seeds.  Their yellow flowers produce nectar and pollen copiously.  Honey derived from these plants granulates readily.  The pollen is highly nutritious for honeybees.  Other vegetable brassicas (cabbage, broccoli, cauliflower, etc.) are not valuable as nectar or pollen plants because they are harvested before flowering occurs.\r\n', '');
INSERT INTO `main` VALUES (172, 'Lupinus spp.', 'Lupine', 'P', 'eSu,mSu', 'W,C,R', 'AB,BC,MB,NB,NF,NS,ON,PE,QC,SK', '', '', '', '', 'Lupines, wild as well as cultivated and crop species, do not produce nectar.  Honeybees sometimes collect the pollen. \r\n', '');
INSERT INTO `main` VALUES (173, 'Viola spp.', 'Violets & Pansy', 'P,N', 'eSp,mSp,lSp,eSu,mSu', 'W,C,R', 'AB,BC,MB,NF,NT,NS,ON,PE,QC,SK,YT', '', '', '', '', 'Some naturalized garden pansies are visited by honeybees for nectar. \r\n', '');
INSERT INTO `main` VALUES (174, 'Asparagus officinale', 'Asparagus', 'P,N', 'mSu', 'W,C,R', 'BC,NB,NS,ON,PE,QC', '', '', '', '', 'he well-known asparagus is grown commercially and escaped plants are established along fence lines, roadsides, and field margins.  Honeybees forage for nectar from the flowers of both male and female plants, and collect pollen from the flowers of male plants.  Not noted as important to honeybees or to beekeeping.  \r\n', '');
INSERT INTO `main` VALUES (175, 'Rubus idaeus', 'Raspberry', 'P,N', 'mSu', 'W,C,R', 'AB,BC,MB,NF,NT,NS,NU,ON,PE,QC,SK,YT', '', '', '', '', 'A common plant of open areas and regenerating forests.  It is a major source of nectar and pollen for many insects, including for honeybees and beekeeping.  Horticultural cultivars are important in fruit production and also provide nectar and pollen to pollinating honeybees when in flower. \r\n', '');
INSERT INTO `main` VALUES (176, 'Rubus occidentalis', 'Raspberry', 'P,N', 'mSu,lSu', 'W,C,R', 'AB,BC,MB,NF,NT,NS,ON,PE,QC,SK,YT', '', '', '', '', 'Quite a common plant of forest margins.  It is source of nectar and pollen for many insects, including for honeybees and beekeeping.  \r\n', '');
INSERT INTO `main` VALUES (177, 'Trifolium pratense', 'Red Clover', 'P,N', 'mSu', 'W,C,R', 'AB,BC,MB,NF,NT,NS,ON,PE,QC,SK,YT', '', '', '', '', 'Cultivated for pasturage, hay and seed production.   It  can be a valued nectar and pollen plant for beekeeping, but the deeply tubular flowers often prevent honeybees from extracting the nectar.  The floral structure is better suited to the attentions of bumblebees. \r\n', '');
INSERT INTO `main` VALUES (178, 'Trifolium repens', 'White Clover', 'P,N', 'mSu', 'W,C,R', 'AB,BC,MB,NF,NT,ON,PE,QC,SK,YT', '', '', '', '', 'Grows in open area and is used for pasture seeding, ground covers along roadsides, and can become naturalized.  It is a valued nectar and pollen plant for beekeeping.\r\n', '');
INSERT INTO `main` VALUES (179, 'Vitis spp.', 'Grapes', 'P', 'mSu', 'W,C,R', 'MB,NB,NS,ON,PE,QC', '', '', '', '', 'The grapes are well known vines.  The cultivated grape, wine or table cultivars, are often used by honeybees as a source of pollen.  Wild grapes are dioecious with male vines producing viable pollen and the female vines producing inviable pollen.  \r\n', '');
INSERT INTO `main` VALUES (180, 'Crocus spp.', 'Crocus', 'P', 'eSp', 'C', 'AB,BC,MB,NB,NF,NS,ON,PE,QC,SK', '', '', '', '', 'Mostly a garden plant, but can become naturalized in urban and suburban places.  Crocus can be important as an early season source of pollen for honeybees.\r\n', '');
INSERT INTO `main` VALUES (181, 'Juglans niger', 'Walnut', 'P', 'eSp', 'W,C', 'MB,ON,QC', '', '', '', '', 'This spring blooming and handsome woodland tree is grown widely in urban settings. It produces copious amounts of pollen.  The pollen is probably not protein rich, but can be eagerly collected by honeybees other pollen sources are scarce.  \r\n', '');
INSERT INTO `main` VALUES (182, 'Symplocarpus foetidus', 'Skunk Cabbage', 'P', 'eSp', 'W,A', 'NB,NS,ON,QC', '', '', '', '', 'Eastern skunk cabbage blooms by producing its own heat and melting through snow and ice in swampy woodlands. On warm days, honeybees visit the blossoms and collect pollen.  \r\n', '');
INSERT INTO `main` VALUES (184, 'Cytisus scoparius', 'Scotch broom', 'P,N', 'eSu', 'R', 'BC,NS,PE', '', '', '', '', 'The brooms do not produce nectar, but some such as Scotch broom are important sources of pollen early in the year where the plant occurs.  It grows in open areas and considerd to be an invasive\r\n', '');
INSERT INTO `main` VALUES (185, 'Elaeagnus hortensis (angustifolia)', 'Russian Olive', 'P,N', 'eSu', 'C', 'AB,BC,MB,NB,NS,ON,QC,SK', '', '', '', '', 'The small, yellowish flowers produce large amounts of nectar and pollen.  It is a valuable plant for beekeepers.  It is localized as a landscape plant in gardens, roadsides.  It is becoming more common as an invasive of uncultivated lands.  \r\n', '');
INSERT INTO `main` VALUES (186, 'Prunus serrulata', 'Japanese cherry', 'P,N', 'eSu', 'C', 'BC,ON', '', '', '', '', 'A cultivated early flowering cherry than is visited by honeybees for nectar and pollen.\r\n', '');
INSERT INTO `main` VALUES (187, 'Wisteria floribunda', 'Wisteria', 'P', 'eSu', 'C', 'BC,ON', '', '', '', '', 'Grown ornamentally, the pendant arrays of fragrant flowers attract honeybees for nectar and pollen.\r\n', '');
INSERT INTO `main` VALUES (188, 'Impatiens capensis', 'Jewel Weed', 'P,N', 'mSu,lSu,eF', 'W,R', 'AB,BC,MB,NB,NF,NS,ON,PE,QC,SK,YT', '', '', '', '', 'This plant, associated with wet areas in forest and shady places.  It produces large amounts of nectar, but it is hidden in the recurved spur at the flower’s base.  It is usually not well used by honeybees for its nectar or pollen, but is well used by bumblebees and humming birds.\r\n', '');
INSERT INTO `main` VALUES (189, 'Quercus spp.', 'Oak', 'P,r', 'eSp,mSp,lSp,eSu,mSu,lSu', 'W,C', 'AB,BC,MB,NF,NT,NS,ON,PE,QC,SK,YT', '', '', '', '', 'Oaks bloom in spring and produce large amounts of pollen, but not every year.  The pollen is probably not protein rich, but can be eagerly collected by honeybees if other pollen sources are scarce.  Sometimes the trees become infested enough with sucking insects, or even pathogenic infections, that honeybees collect large amounts of honeydew from them.\r\n', '');
INSERT INTO `main` VALUES (190, 'Aesculus spp.', 'Buckeye', 'P,N,r', 'mSu', 'C', 'BC,NB,ON,QC', '', '', '', '', 'There are a number of species, hybrids and cultivars used as ornamentals.  Some such as the Ohio buckeye, A. glabra, are valued for nectar and honey production.\r\n', '');
INSERT INTO `main` VALUES (191, 'Fragaria virginiana', 'Strawberry', 'P,N', 'mSu', 'W,C', 'AB,BC,MB,NF,NT,NS,NU,ON,PE,QC,SK,YT', '', '', '', '', 'Wild strawberry blossoms are used by honeybees as sources of nectar and pollen.\r\n', '');
INSERT INTO `main` VALUES (192, 'Sinapis alba', 'White mustard', 'P,N', 'mSu,lSu,eF,mF', 'C,R', 'AB,BC,MB,NB,NS,ON,PE,QC,SK,YT', '', '', '', '', 'Mustard is grown extensively as green manure and as a plant for seed.  The white flowers  produce nectar and pollen.  Honey derived from these plants (and other brassicas) tends to granulate readily.\r\n', '');
INSERT INTO `main` VALUES (193, 'Stachys palustris', 'Marsh hedge-nettle', 'P,N', 'mSu', 'W,A', 'MB,NB,NF,NS,ON,PE,QC', '', '', '', '', '', '');
INSERT INTO `main` VALUES (194, 'Lythrum salicaria', 'Purple loosestrife', 'P,N', 'mSu,lSu', 'R,A', 'AB,BC,MB,NB,NS,ON,PE,QC,SK', '', '', '', '', 'A widespread invasive plant of wetlands that supplies abundant nectar and pollen to honeyebees and beekeeping. \r\n', '');
INSERT INTO `main` VALUES (195, 'Phaseolus coccineus', 'Scarlet runner bean', 'P,N', 'lSu', 'C', 'AB,BC,MB,NB,NF,NS,ON,PE,QC,SK', '', '', '', '', 'The flowers of scarlet runner been are too large for honeybees to use for either pollen or nectar.  If nectar accumulates enough in the flowers, the honeybees can sometimes reach it. \r\n', '');
INSERT INTO `main` VALUES (196, 'Thymus', ' Thyme', 'P,N', 'lSu', 'C', 'AB,BC,MB,NB,NF,NS,ON,PE,QC,SK', '', '', '', '', 'The thymes, cultivated and wild, grow in open areas. They have flowers that produce moderate amounts of nectar that is collected by honeybees.  \r\n', '');
INSERT INTO `main` VALUES (197, 'Vicia faba', 'Broad bean', 'P,N', 'lSu', 'C', 'AB,BC,MB,NB,NF,NS,ON,PE,QC,SK', '', '', '', '', 'This crop plant is very variable in terms of it flowers.  Some cultivars have flowers too large for honeybees to work for nectar, unless blumblebees have already pierced to base of the flower.  Even so, it is regarded as a valuable honey plant.  Sometimes aphis populations are large enough for honeydew to be attractive to hoenyebees.  Honeybees gather gray-green pollen loads. \r\n', '');
INSERT INTO `main` VALUES (198, 'Althea (Alcea) rosea', 'Hollyhock', 'P', 'mSu', 'C', 'AB,BC,MB,NB,NF,NS,ON,PE,QC,SK', '', '', '', '', 'Commonly grown garden plant. Produces pollen copiously, can attract many honeybees, but may also be ignored by them. Well used by bumblebees. Flora nectar is gathered by honeybees, as may be honeydew from sap-sucking insects if numerous on the plants. \r\n', '');
INSERT INTO `main` VALUES (199, 'Antirrhinum majus', 'Snapdragon', 'P,N', 'mSu', 'C', 'AB,BC,MB,NB,NF,NS,ON,PE,QC,SK', '', '', '', '', 'Commonly grown garden plant, produces abundant nectar and pollen.  It is well used by \r\n', '');
INSERT INTO `main` VALUES (200, 'Astragalus cicer', 'Milkvetch', 'P,N', 'mSu', 'C', 'AB,MB', '', '', '', '', 'This plant is sometimes grown as a cover crop and for soil improvement.  It is an attractive nectar provider to honeybees.\r\n', '');
INSERT INTO `main` VALUES (201, 'Azalea (Rhododendron)', 'Azalea & Rhododendron', 'N', 'eSp,mSp,lSp,eSu,mSu,lSu,eF,mF,lF', 'C', 'AB,BC,MB,NB,NF,NT,NS,NU,ON,PE,QC,SK,YT', '', '', '', '', 'There are several species and many cultivars of these shrubs used horticulturally.  Those, and the wild species are not known as important for honeybees, but some are well used by bumblebees as sources of nectar and pollen. \r\n', '');
INSERT INTO `main` VALUES (202, 'Catalpa speciosa', 'Catalpa', 'P,N', 'mSu', 'C', 'BC,ON,QC', '', '', '', '', 'This widely grown ornamental tree has flowers suited to the visits of bumblebees. Although the flowers produce nectar and pollen, it is not known as an important plant for beekeeping.\r\n', '');
INSERT INTO `main` VALUES (203, 'Cosmos bipinnatus', 'Cosmos', 'P,N', 'mSu,lSu,eF,mF', 'C', 'BC,ON,QC', '', '', '', '', 'This garden plant can be an important source of nectar, especially late in the summer and early fall.  It is not known for producing much pollen. \r\n', '');
INSERT INTO `main` VALUES (204, 'Cucumis sativa', 'Cucumber', 'P,N', 'mSu,lSu,eF,mF', 'C', 'BC,NB,NS,ON,PE,QC', '', '', '', '', 'Field cucumbers produce nectar in abundance and it is sometimes gathered by honeybees to produce “floral source” honey.  The pollen, produced only on male flowers, is spiny and sometimes ignored or groomed off by foraging honeybees.\r\n', '');
INSERT INTO `main` VALUES (205, 'Cucurbita spp.', 'Pumpkin', 'P,N', 'mSu', 'C', 'ON,QC', '', '', '', '', '', '');
INSERT INTO `main` VALUES (206, 'Digitalis purpurea', 'Foxglove', 'P,N', 'mSu,lSu,eF', 'C', 'AB,BC,MB,NB,NF,NS,ON,PE,QC,SK', '', '', '', '', 'This garden plant can be an important local source of nectar for honeybees.  Each flowering can produce enough nectar to make a load for a honeybee. It produces pollen, but the pollen is reputedly poisonous to honeybees .  The flowers are well visited and worked by bumblebees.\r\n', '');
INSERT INTO `main` VALUES (207, 'Fagopyrum esculentum', 'Buckwheat', 'P,N', 'mSu', 'C', 'AB,BC,MB,NB,NF,NS,ON,PE,QC,SK', '', '', '', '', 'This crop plant is highly valued by beekeepers for the dark, characteristic honey that originates from its flowers.  The flowers are not prolific nectar producers, and yields may be variable from time to time and place to place.   Nectar flow is characteristically in the morning.  The value of buckwheat for pollen is low.  The flowers produce rather little, and it may by poisonous to honeybees. \r\n', '');
INSERT INTO `main` VALUES (208, 'Glycine max', 'Soya bean', 'N', 'mSu', 'C', 'ON,QC', '', '', '', '', 'This crop has been reported to provide excellent honey crops in some places.  It is probably that different cultivars differ in their nectar production.  The flowers are mostly or fully self-fertilizing and produce little pollen.\r\n', '');
INSERT INTO `main` VALUES (209, 'Ligustrum japonicum', 'Japanese privet', 'P,N', 'mSu', 'C', 'MB,NB,NS,ON,PE,QC', '', '', '', '', 'Privet is grown mostly as hedges.  Its white panicles of flowers produce nectar, but the resulting honey is reputed to taste off.  Honeybees forage for pollen from the flowers.\r\n', '');
INSERT INTO `main` VALUES (210, 'Medicago sativa', 'Alfalfa', 'P,N', 'mSu,lSu,eF', 'C', 'AB,BC,MB,NF,NT,NS,ON,PE,QC,SK,YT', '', '', '', '', 'Grown extensively for seed and hay.  It is naturalized in open and uncultivated areas. It is a highly valued plant for beekeeping, producing nectar for high quality honey.  Honeybees often tend not to gather the pollen.\r\n', '');
INSERT INTO `main` VALUES (211, 'Phaseolus vulgaris', 'Garden Bean', 'P,N', 'mSu', 'C', 'AB,BC,MB,NB,NF,NS,ON,PE,QC,SK', '', '', '', '', 'Flowers of garden beans produce nectar and pollen that are collected by honeybees\r\n', '');
INSERT INTO `main` VALUES (212, 'Prunus cerasus', 'Sour cherry', 'P,N', 'mSu', 'C', 'BC,NB,NS,ON,PE,QC', '', '', '', '', 'This early blooming orchard tree provides nectar and pollen to honeybees.\r\n', '');
INSERT INTO `main` VALUES (213, 'Prunus domestica', 'Plum', 'P', 'eSp,mSp,lSp', 'C', 'BC,NB,NF,NS,ON,QC', '', '', '', '', 'This early blooming orchard tree (one of the first of the stone fruits) provides nectar and pollen to honeybees.\r\n', '');
INSERT INTO `main` VALUES (214, 'Prunus persica', 'Peach', 'P,N', 'eSp,mSp,lSp', 'C', 'BC,ON', '', '', '', '', 'This early blooming orchard tree provides nectar and pollen to honeybees.\r\n', '');
INSERT INTO `main` VALUES (215, 'Pyrus communis', 'Pear', 'P,N', 'eSp,mSp,lSp', 'C', 'BC,NB,NS,ON,QC', '', '', '', '', 'Pear blossoms are well known as not being eagerly worked by honeybees.  The nectar tends to be watery. Honeybees foraging on pear blossoms collect pollen which is rich in protein.\r\n', '');
INSERT INTO `main` VALUES (216, 'Salvia officinalis', 'Sage', 'P,N', 'mSu', 'C', 'AB,BC,MB,NF,NT,NS,NU,ON,PE,QC,SK,YT', '', '', '', '', 'The sages, cultivated and wild, grow in open areas. They have flowers that produce moderate amounts of nectar that is collected by honeybees.  \r\n', '');
INSERT INTO `main` VALUES (217, 'Trifolium hybridum', 'Alsike Clover', 'P,N', 'mSu', 'C', 'AB,BC,MB,NF,NT,NS,ON,PE,QC,SK,YT', '', '', '', '', 'Grows in open area and is used for pasture seeding, ground covers along roadsides, and can become naturalized.  It is a valued nectar and pollen plant for beekeeping. \r\n', '');
INSERT INTO `main` VALUES (218, 'Vicia lathyroides', 'Spring vetch', 'P,N', 'mSu', 'C', 'BC,MB,NB,NF,NS,ON,PE,QC,YT', '', '', '', '', '', '');
INSERT INTO `main` VALUES (219, 'Vicia villosa', 'Hairy vetch', 'P,N', 'mSu,lSu,eF', 'C', 'BC,MB,NB,NS,ON,PE,QC', '', '', '', '', 'This plant grows in open areas and in cultivation. It can be used as a cover plant to support pollinating bees in orchards. Honeybees gather its nectar and pollen.  It is regarded as an important plant for beekeepers under some circumstances.\r\n', '');
INSERT INTO `main` VALUES (220, 'Zea mays', 'Maize', 'P', 'mSu', 'C', 'MB,ON,QC', '', '', '', '', 'Honeybees use corn (maize) extensively as a source of pollen.  They work the tassels in the morning as the anthers dehisce.  Honeybees may also drink water (dew) that collects on the foliage.   This crop can be a major source of pesticide poisoning for beekeepers.\r\n', '');
INSERT INTO `main` VALUES (221, 'Ajuga reptans', 'Common bugle', 'P,N', 'lSp,eSu,mSu', 'R', 'BC,NF,NS,ON,QC', '', '', '', '', 'A weedy plant that grows along roadsides, vacant land, and sometimes in uncultivated fields. A good source of nectar.\r\n', '');
INSERT INTO `main` VALUES (222, 'Campanula rapunculoides', 'Bellflower', 'P,N', 'mSu,lSu,eF,mF', 'R', 'AB,BC,MB,NB,NF,NS,ON,PE,QC,SK', '', '', '', '', '', '');
INSERT INTO `main` VALUES (223, 'Mentha spicata', 'Spear mint', 'P,N', 'mSu,lSu,eF', 'R', 'AB,BC,MB,NB,NS,ON,PE,QC,SK', '', '', '', '', '', '');
INSERT INTO `main` VALUES (224, 'Sonchus spp.', 'Sow Thistle', 'P,N', 'mSu,lSu', 'R', 'AB,BC,MB,NB,NF,NT,NS,ON,PE,QC,SK,YT', '', '', '', '', '', '');
INSERT INTO `main` VALUES (225, 'Vicia angusifolia', 'Common Vetch', 'P,N', 'mSu,lSu,eF,mF', 'R', 'BC,MB,NB,NF,NS,ON,PE,QC,YT', '', '', '', '', '', '');
INSERT INTO `main` VALUES (226, 'Arctiumlappa and A. minus', 'Burdock', 'P,N', 'mSu,lSu,eF,mF', 'R', 'AB,BC,MB,NB,ON,QC,SK', '', '', '', '', 'Familiar, large-leaved weeds with purplish flowering heads.  Not noted as important for honeybees, but well used by bumblebees for both nectar and pollen over its long blooming period from mid-summer to fall.\r\n', '');
INSERT INTO `main` VALUES (227, 'Carumcarvi', 'Caraway', 'P,N', 'mSu', 'R', 'AB,BC,MB,NB,NF,NS,ON,PE,QC,SK', '', '', '', '', 'Caraway is grown for its seeds.  Its flowers produce nectar and pollen that is used by honeybees, but only very locally.\r\n', '');
INSERT INTO `main` VALUES (228, 'Centaurea spp.  ', 'Knapweeds, Cornflowers', 'P,N', 'lSu,eF', 'R', 'AB,BC,MB,NB,NF,NS,ON,PE,QC,SK', '', '', '', '', 'Although valuable as honey plants, these mostly weedy species are only minor producers of pollen.  Honeybees often forage from the flowers throughout the day. They grow prolifically on roadsides, rights of way, and uncultivated open areas.  The common cornflower, a popular garden plant, attracts many flower visiting insects.\r\n', '');
INSERT INTO `main` VALUES (229, 'Cirsium arvensis', 'Canada Thistle', 'P,N', 'mSu,lSu,eF,mF,lF', 'R', 'AB,BC,MB,NB,NF,NT,NS,ON,PE,QC,SK,YT', '', '', '', '', 'A weedy plant that grows along roadsides, vacant land, pastures and in uncultivated fields. A good source of nectar and pollen.\r\n', '');
INSERT INTO `main` VALUES (230, 'Cyanoglossum officinale', 'Hounds Tongue', 'P,N', 'eSu,mSu', 'R', 'AB,BC,MB,NB,NS,ON,QC,SK', '', '', '', '', 'Hound’s tongue is poorly known as a nectar plant for honeybees, but this widely dispersed weed is probably quite important.\r\n', '');
INSERT INTO `main` VALUES (231, 'Daucus carota', 'Carrot/Queen Anne’s Lace', 'P,N', 'mSu,lSu,eF,mF', 'R', 'AB,BC,MB,NB,NF,NS,NU,ON,PE,QC,SK,YT', '', '', '', '', 'A weedy plant that grows in abundance along roadsides, vacant land, and sometimes in uncultivated fields. A good source of nectar and pollen.\r\n', '');
INSERT INTO `main` VALUES (232, 'Diplotaxis tenuifolia', 'Wall rocket', 'P,N', 'mSu,lSu,eF,mF', 'R', 'BC,NB,NS,ON,QC', '', '', '', '', 'Wall rocket is an adventive weed in the mustard family that grows in stony and waste places.  It produces nectar and pollen that is sometimes taken by honeybees. \r\n', '');
INSERT INTO `main` VALUES (233, 'Dipsacus sylvestris', 'Teasel', 'P,N', 'mSu,lSu', 'R', 'BC,ON,QC', '', '', '', '', 'This highly distinctive plant now grows mostly along roadsides and other uncultivated lands.  It produces large amounts of nectar which honeybees seek out eagerly. It is also a good source of pollen.  It is regarded by some as one of the very best bee plants in existence.\r\n', '');
INSERT INTO `main` VALUES (234, 'Echium vulgare', 'Blueweed/Vipers Bugloss', 'P,N', 'mSu,lSu,eF,mF', 'R', 'AB,BC,MB,NB,NF,NS,ON,PE,QC,SK', '', '', '', '', 'Although this plant is a notorious weed and grows prolifically along roadsides, rights of way, and unkempt land, it is a highly important and productive nectar plant.  It also produces a great amount of pollen that is well foraged by honeybees. \r\n', '');
INSERT INTO `main` VALUES (235, 'Euphorbia esula', 'Leafy spurge', 'P,N', 'mSu,lSu,eF', 'R', 'AB,BC,MB,NB,NS,ON,PE,QC,SK,YT', '', '', '', '', 'Leafy spurge is a well-known weed that  produces large amounts of extrafloral nectar that is fed on by a wide variety of pollinating and other insects. The nectar can become very sticky by evaporation.  Honeybees are not known to use this source of nectar.  Some spurges are used by honeybees to make characteristically tasting honey.\r\n', '');
INSERT INTO `main` VALUES (236, 'Hypericum perforatum', 'St. John’s wort', 'P,N', 'mSu', 'R', 'BC,MB,NB,NF,NS,ON,PE,QC', '', '', '', '', 'A common weed sometimes visited for pollen and nectar by honeybees. \r\n', '');
INSERT INTO `main` VALUES (237, 'Ipomoea purpurea', 'Morning glory', 'P,N', 'lSu,eF,mF,lF', 'R', 'ON,QC', '', '', '', '', 'A garden plant sometimes used by honeybees as a source of nectar\r\n', '');
INSERT INTO `main` VALUES (238, 'Lathyrus tuberosus', 'Tuberous vetchling', 'P,N', 'mSu', 'R', 'MB,ON,QC', '', '', '', '', 'A low growing plant that spreads vigorously on banks, roadsides, and other open areas.  Honeybees use the flowers for nectar and pollen collection.\r\n', '');
INSERT INTO `main` VALUES (239, 'Leonuris cardiaca', 'Mother wort', 'P,N', 'mSu,lSu,eF', 'R', 'AB,BC,MB,NB,NS,ON,PE,QC,SK', '', '', '', '', 'A weedy plant that grows in abundance along roadsides, vacant land, and sometimes in uncultivated fields. A good source of nectar and pollen.\r\n', '');
INSERT INTO `main` VALUES (240, 'Linaria vulgaris', 'Toadflax', 'P,N', 'mSu,lSu,eF', 'R', 'AB,BC,MB,NB,NF,NS,ON,PE,QC,SK', '', '', '', '', 'A widespread weed that produces abundant nectar and pollen.  The deep, tubular flowers are well suited to bumblebees but not for honeybees.\r\n', '');
INSERT INTO `main` VALUES (241, 'Malva moschata', 'Musk mallow', 'P,N', 'mSu,lSu,eF,mF', 'R', 'BC,MB,NB,NF,NS,ON,PE,QC', '', '', '', '', 'This naturalized plant grows on limestone dominated soils on uncultivated lands and  along roadsides. Honeybees forage on the flowers for nectar and pollen.\r\n', '');
INSERT INTO `main` VALUES (242, 'Medicago lupulinus', 'Black medick', 'P,N', 'mSu,lSu,eF', 'R', 'AB,BC,MB,NB,NF,NT,NS,ON,PE,QC,SK,YT', '', '', '', '', 'Black medic is not regarded as important of honeybees for either nectar or pollen. \r\n', '');
INSERT INTO `main` VALUES (243, 'Melilotusindica', 'Small-flowered sweet clover', 'P,N', 'mSu,lSu,eF', 'R', 'BC,MB,NS', '', '', '', '', 'This plant, in cultivation or growing wild along roadsides and on untended land, is well regard for its value to beekeeping.  It produces abundant nectar valued for the quality of honey that results. The pollen is not so well sought after by honeybees.\r\n', '');
INSERT INTO `main` VALUES (244, 'Pastinaca sativa', 'Wild parsnip', 'P,N', 'mSu,lSu,eF', 'R', 'AB,BC,MB,NB,NF,NT,NS,ON,PE,QC,SK,YT', '', '', '', '', 'The common weedy wild parsnips produce large amounts of nectar and pollen, though only small amounts from each flower, of which there are hundreds.  It is probably an important weedy plant for honeybees and beekeeping.\r\n', '');
INSERT INTO `main` VALUES (245, 'Taraxacum officinale', 'Dandelion', 'P,N', 'eSp,mSp,lSp,eSu', 'R', 'AB,BC,MB,NF,NT,NS,NU,ON,PE,QC,SK,YT', '', '', '', '', 'A much reviled weed, its nectar and pollen are important to beekeeping.\r\n', '');
INSERT INTO `main` VALUES (246, 'Tragopogon dubius', 'Salsify', 'P,N', 'mSu', 'R', 'AB,BC,MB,NF,NT,NS,ON,PE,QC,SK,YT', '', '', '', '', 'Weedy plants of roadsides and waste land.  Not considered important to honeybees for either nectar or pollen, though they are known to visit the flowering heads to forage.\r\n', '');
INSERT INTO `main` VALUES (247, 'Vicia cracca', 'Cow vetch', 'P,N', 'eSu,mSu,lSu', 'R', 'AB,BC,MB,NB,NF,NT,NS,NU,ON,PE,QC,SK,YT', '', '', '', '', 'This widespread weedy and viney species is used by honeybees for its floral nectar and sometimes for pollen. \r\n', '');
INSERT INTO `main` VALUES (248, 'Vaccinium spp.', 'Blueberry (several species)', 'N', 'mSu', 'W,C', 'AB,BC,MB,NF,NT,NS,NU,ON,PE,QC,SK,YT', '', '', '', '', '', '');
INSERT INTO `main` VALUES (249, 'Apocynum cannabinum', 'Dogbane, Indian Hemp', 'P,N', 'mSu,lSu', 'W,R', 'AB,BC,MB,NB,NF,NT,NS,ON,PE,QC,SK,YT', '', '', '', '', '', '');
INSERT INTO `main` VALUES (250, 'Cleome serrulata', 'Bee plant', 'P,N', 'mSu,lSu,eF,mF', 'W,R', 'AB,BC,MB,NT,ON,QC,SK', '', '', '', '', '', '');
INSERT INTO `main` VALUES (251, 'Ribes uva-crispa', 'Gooseberry', 'P,N', 'eSu,mSu', 'W,C', 'AB,BC,MB,NF,NT,NS,NU,ON,PE,QC,SK,YT', '', '', '', '', '', '');
INSERT INTO `main` VALUES (252, 'Rhus spp.', 'Sumacs, Poison Ivy & Oak', 'P,N', 'mSu', 'W,R', 'AB,BC,MB,NB,NS,ON,PE,QC,SK', '', '', '', '', '', '');
INSERT INTO `main` VALUES (253, 'Rubus fruticosus spp.', 'Blackberry', 'P,N', 'mSu', 'W,C', 'AB,BC,MB,NF,NT,NS,NU,ON,PE,QC,SK,YT', '', '', '', '', '', '');
INSERT INTO `main` VALUES (254, 'Senecio spp.', 'Groundsel', 'P,N', 'mSu,lSu', 'W,R', 'AB,BC,MB,NF,NT,NS,NU,ON,PE,QC,SK,YT', '', '', '', '', '', '');
INSERT INTO `main` VALUES (255, 'Solidago canadensis & other spp.', 'Goldenrod', 'P', 'lSu,eF,mF,lF', 'W,R', 'AB,BC,MB,NB,NF,NT,NS,NU,ON,PE,QC,SK,YT', '', '', '', '', '', '');
INSERT INTO `main` VALUES (256, 'Acer spp.', 'Maple', 'P,N', 'eSp,mSp,lSp,eSu', 'W,C', 'AB,BC,MB,NF,NT,NS,ON,PE,QC,SK,YT', '', '', '', '', 'Some maples have flowers that are rarely visited by honeybees for pollen, and produce little or no nectar.  \r\n', '');
INSERT INTO `main` VALUES (257, 'Chamaenerion (now Chamerion) angustifolium', 'Fireweed', 'N', 'mSu,lSu,eF,mF', 'W,R', 'AB,BC,MB,NF,NT,NS,NU,ON,PE,QC,SK,YT', '', '', '', '', 'Grows well in open areas, especially after forest fires.  It can form huge stands with flowers that secrete large amounts of nectar.  Honeybees tend not to collect its pollen with its grains held together loosely with strands of viscin. \r\n', '');
INSERT INTO `main` VALUES (258, 'Viburnum opulus ', 'European cranberry bush', 'P,N', 'mSu', 'C,R', 'AB,BC,MB,NB,NF,NS,ON,PE,QC,SK', '', '', '', '', 'A fast growing shrub of roadsides and open areas., sometimes grown as an ornamental.  Its flowers are used by honeybees as a source of nectar.\r\n', '');
INSERT INTO `main` VALUES (259, 'Borago officinalis', 'Borage', 'P,N', 'mSu,lSu,eF,mF', 'C,R', 'AB,BC,MB,NB,NF,NS,ON,PE,QC,SK', '', '', '', '', 'As a crop and garden plant, it can be an important local source of high quality nectar.  The long blooming season can be especially valuable. Pollen grains are small and the plant may be ignored as a pollen source\r\n', '');
INSERT INTO `main` VALUES (260, 'Cirsium spp.', 'Thistles', 'P,N', 'mSu,lSu,eF', 'W,R', 'AB,BC,MB,NF,NT,NS,ON,PE,QC,SK,YT', '', '', '', '', 'Generally thistles are good sources of nectar and pollen.\r\n', '');
INSERT INTO `main` VALUES (261, 'Echinocystus lobata', 'Wild Cucumber', 'P,N', 'mSu,lSu', 'W,R', 'AB,BC,MB,NB,NS,ON,PE,QC,SK', '', '', '', '', 'The fast-growing, soft, climbing vines produce clusters of white flowers, most of which are male and produce pollen that may be taken by honeybees at times.  \r\n', '');
INSERT INTO `main` VALUES (262, 'Foeniculum vulgare', 'Fennel', 'P,N,r', 'mSu', 'W,R', 'AB,BC,ON,QC', '', '', '', '', 'A weedy plant that grows along roadsides, vacant land, and sometimes in uncultivated fields. A good source of nectar and pollen. It is also grown for seeds and as a herb.\r\n', '');
INSERT INTO `main` VALUES (263, 'Helianthus annuus', 'Sunflower', 'P,N', 'mSu,lSu,eF', 'W,C', 'AB,BC,MB,NF,NS,ON,PE,QC,SK', '', '', '', '', 'This crop yields much nectar and is an excellent honey plant.  Nectar is secreted mostly by the florets making up the huge heads, but the plants also have extra-floral nectaries that attract honeybees.  Sometimes honeybees collect the gum produced by the involucre of the heads, probably for propolis production.  Some wild sunflowers may grow prolifically in uncultivated open lands where they are well used by honeybees as sources of nectar.  Sunflower pollen is large and spiny and usually groomed from the bodies of nectar foraging honeybees. \r\n', '');
INSERT INTO `main` VALUES (264, 'Malus domestica', 'Apple', 'P,N', 'eSp,mSp,lSp', 'W,C', 'AB,BC,MB,NF,NT,NS,ON,PE,QC,SK,YT', '', '', '', '', 'Apple blossoms are well known as being eagerly worked by honeybees for nectar and pollen.\r\n', '');
INSERT INTO `main` VALUES (265, 'Melilotus alba', 'Sweet Clover White', 'P,N', 'mSu,lSu,eF', 'W,R', 'AB,BC,MB,NF,NT,ON,PE,QC,SK,YT', '', '', '', '', 'This plant, in cultivation or growing wild along roadsides and on untended land, is well regard for its value to beekeeping.  It produces abundant nectar valued for the quality of honey that results. The pollen is not so well sought after by honeybees.\r\n', '');
INSERT INTO `main` VALUES (266, 'Melilotus officinalis', 'Sweet Clover Yellow', 'P,N', 'mSu,lSu,eF', 'W,R', 'AB,BC,MB,NF,NT,NS,ON,PE,QC,SK,YT', '', '', '', '', 'This plant, in cultivation or growing wild along roadsides and on untended land, is well regard for its value to beekeeping.  It produces abundant nectar valued for the quality of honey that results. The pollen is not so well sought after by honeybees.\r\n', '');
INSERT INTO `main` VALUES (267, 'Physostegia virginiana', 'Obedient plant', 'P,N', 'mSu', 'W,C', 'AB,BC,MB,NB,NF,NS,ON,PE,QC,SK', '', '', '', '', 'Grows well as a garden ornamental in shady places.  In the wild it is associated with damp thickets, swamps and prairies.  The flowers produce nectar abundantly.  It is not known as a pollen plant.\r\n', '');
INSERT INTO `main` VALUES (268, 'Prunus serotina', 'Wild Cherry', 'P,N', 'lSp,eSu', 'W,R', 'BC,NB,NS,ON,QC', '', '', '', '', 'This native forest tree blooms prolifically. It may be well visited by honeybees for nectar and pollen, but itspossible importance in localized situations is unknown. \r\n', '');
INSERT INTO `main` VALUES (269, 'Raphanus raphinastrum', 'Radish', 'P,N', 'mSu,lSu', 'W,R', 'AB,BC,MB,NB,NF,NS,ON,PE,QC,SK', '', '', '', '', 'Wild raddish can be a troublesome weed in fields. It is not recorded as important for its nectar or pollen for honeybees.\r\n', '');
INSERT INTO `main` VALUES (270, 'Clematis virginiana', 'Clematis', 'P', 'lSu,eF', 'W,R', 'MB,NB,NS,ON,PE,QC', '', '', '', '', 'Climbing plant of forest margins, thickets, and fence lines.  The flowers seem not to produce nectar.  The plant is dioecious (separate sexes) and honeybees collect pollen from male plants.  Ornamental Clematis species are used by honeybees for pollen.  The flowers do not have petals.  The floral array is made up of the coloured sepals. \r\n', '');
INSERT INTO `main` VALUES (271, 'Coronilla varia', 'Crown vetch', 'P', 'mSu,lSu,eF', 'C,R', 'AB,BC,MB,NB,NF,NS,ON,PE,QC,SK', '', '', '', '', 'Grows on waste land, roadsides, and other uncultivated rough habitats.  Noted as poor as a nectar plant, but useful to beekeeping for its pollen.\r\n', '');
INSERT INTO `main` VALUES (272, 'Corylus spp.', 'Hazelnut', 'P', 'eSp,mSp', 'W,C', 'AB,BC,MB,NB,NF,NS,ON,PE,QC,SK', '', '', '', '', 'These early blooming trees of woodland margins and thickets produce large amounts of pollen in early spring.  The pollen is probably not protein rich, but can be eagerly collected by honeybees when no other pollen sources are available.  \r\n', '');
INSERT INTO `main` VALUES (273, 'Thalictrum spp.', 'Meadow rue', 'P', 'mSp,lSp,eSu,mSu,lSu', 'W,R', 'AB,BC,MB,NB,NF,NS,ON,PE,QC,SK', '', '', '', '', 'The meadow rues are very diverse in species and habits. Honeybees work the flowers for pollen.\r\n', '');
INSERT INTO `main` VALUES (274, 'Populus spp.', 'Aspen', 'P,r', 'eSp,mSp,lSp', 'W,C', 'AB,BC,MB,NF,NT,NS,ON,PE,QC,SK,YT', '', '', '', '', 'Poplar trees can be an important source of pollen to honeybees early in spring and summer.  They do not produce nectar.  Honeybees gather gum from the buds and other parts which they use for making propolis. \r\n', '');

-- --------------------------------------------------------

-- 
-- Table structure for table `plant_type`
-- 

CREATE TABLE `plant_type` (
  `ID` int(11) NOT NULL auto_increment,
  `code` varchar(8) NOT NULL,
  `plant_type` varchar(32) NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

-- 
-- Dumping data for table `plant_type`
-- 

INSERT INTO `plant_type` VALUES (1, 'U', 'Widespread');
INSERT INTO `plant_type` VALUES (2, 'W', 'Wild (Native or Escaped)');
INSERT INTO `plant_type` VALUES (3, 'C', 'Cultivated or Crop');
INSERT INTO `plant_type` VALUES (4, 'R', 'Weedy');
INSERT INTO `plant_type` VALUES (5, 'A', 'Wetlands');

-- --------------------------------------------------------

-- 
-- Table structure for table `season`
-- 

CREATE TABLE `season` (
  `ID` int(11) NOT NULL auto_increment,
  `Season_Code` varchar(16) NOT NULL,
  `Season_Description` varchar(32) NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

-- 
-- Dumping data for table `season`
-- 

INSERT INTO `season` VALUES (1, 'Sp', 'Spring');
INSERT INTO `season` VALUES (2, 'Su', 'Summer');
INSERT INTO `season` VALUES (3, 'F', 'Fall');

-- --------------------------------------------------------

-- 
-- Table structure for table `subseason`
-- 

CREATE TABLE `subseason` (
  `ID` int(11) NOT NULL auto_increment,
  `Subseason_Code` varchar(16) NOT NULL,
  `Subseason_Description` varchar(32) NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

-- 
-- Dumping data for table `subseason`
-- 

INSERT INTO `subseason` VALUES (1, 'e', 'Early');
INSERT INTO `subseason` VALUES (2, 'm', 'Mid');
INSERT INTO `subseason` VALUES (3, 'l', 'Late');
INSERT INTO `subseason` VALUES (4, 'f', 'Full');
