How to set up Seeds of Diversity's web site(s)

1. Windows: Install Uniform Server

Download the most recent version of Uniform Server from SourceForge.  Get there from www.uniformserver.com
Get the ZIP file version.
Unzip the file to any place you like.  e.g. c:\bin\UniServer

2. Windows: Install a SubVersion client

We recommend TortoiseSVN (www.tortoisesvn.net) or Eclipse with Subclipse and PDT/PHPEclipse

3. Checkout our code from our SubVersion server, into c:\bin\UniServer\www

Windows with Tortoise: Right-click in the www directory and choose SVN Checkout...
                       For URL of repository, enter https://ssl.peaceworks.ca/svn/seeds/trunk
                       For Checkout directory, use c:\bin\UniServer\www\seeds
                       For Checkout Depth, choose Fully recursive
                       For Revision, use HEAD

Linux: cd to a directory under your Apache root, or set Apache to point to a directory of your choice. We'll call it www.
       In the www/seeds directory, "svn checkout https://ssl.peaceworks.ca/svn/seeds/trunk/ ."

You will need a userid and password for the Subversion repository.

4. In your web browser, go to http://localhost/seeds/seeds.ca
   That should look like Seeds of Diversity's home page.

5. Create databases and fill them with sample data

Point your web browser at http://localhost/seeds/seeds.ca/int/siteSetup.php
This will help you to set up the databases. It also tests the installation for missing pieces.
There are several web sites in the installation, and you configure each one
separately. However, they are designed to co-exist in a development
installation so feel free to install them all one after the other.

Your first job will be to open a MySql client and issue the commands to create
databases and users. The setup script tests for these, and tells you what to
do at each step.

