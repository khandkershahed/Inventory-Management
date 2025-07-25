<!doctype html>
<!--[if IE 6 ]><html lang="en-us" class="ie6"> <![endif]-->
<!--[if IE 7 ]><html lang="en-us" class="ie7"> <![endif]-->
<!--[if IE 8 ]><html lang="en-us" class="ie8"> <![endif]-->
<!--[if (gt IE 7)|!(IE)]><!-->
<html lang="en-us">
<!--<![endif]-->

<head>
    <meta charset="utf-8">
    <title>{{optional($general_setting)->site_title}}</title>
    <meta name="description" content="">
    <meta name="author" content="{{optional($general_setting)->developed_by}}">
    <meta name="copyright" content="{{optional($general_setting)->developed_by}}">
    <meta name="generator" content="Documenter v2.0 http://rxa.li/documenter">
    <meta name="date" content="2017-04-26T00:00:00+02:00">
    <link rel="icon" type="image/png" href="{{url('logo', optional($general_setting)->site_logo)}}" />
    <!-- Google fonts - Roboto -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:400,700">
    <link rel="stylesheet" href="read_me/assets/css/documenter_style.css" media="all">
    <link rel="stylesheet" href="read_me/assets/css/jquery.mCustomScrollbar.css" media="all">

    <script src="read_me/assets/js/jquery.js"></script>
    <script src="read_me/assets/js/jquery.scrollTo.js"></script>
    <script src="read_me/assets/js/jquery.easing.js"></script>
    <script src="read_me/assets/js/jquery.mCustomScrollbar.js"></script>
    <script>
    document.createElement('section');
    var duration = '500',
        easing = 'swing';
    </script>
    <script src="read_me/assets/js/script.js"></script>
</head>

<body>
    <div id="documenter_sidebar">
    	<a href="{{ URL::previous() }}"><img src="{{url('logo', optional($general_setting)->site_logo)}}" style="border: none;margin: 20px 20px 0;width: 180px"></a>
        <ul id="documenter_nav">
            <li><a class="current" href="#documenter_cover">Start</a></li>
            <li><a href="#server_requirement" title="SERVER REQUIREMENTS">Server Requirements</a></li>
            <li><a href="#install" title="INSTALL">Install</a></li>
            <li><a href="#addon" title="Addon">Addon</a></li>
            <li><a href="#common-error" title="Common error">Common Error</a></li>
            <li><a href="#update" title="SOFTWARE UPDATE">Software Update</a></li>
            <li><a href="#pos-printer" title="POS Printer Configuration">POS Printer Settings</a></li>
            <li><a href="#empty-db" title="EMPTY DATABASE">Empty Database</a></li>
            <li><a href="#mail" title="SETUP MAIL SERVER">Setup Mail Server</a></li>
            <li><a href="#dashboard" title="DASHBOARD">Dashboard</a></li>
            <li><a href="#product" title="PRODUCT">Product</a></li>
            <li><a href="#weight-scale-machine" title="Weight Scale Machine">Weight Scale Machine</a></li>
            <li><a href="#print-barcode" title="Print Barcode">Print Barcode</a></li>
            <li><a href="#adding-stock" title="Adding Stock">Adding Stock</a></li>
            <li><a href="#purchase" title="PURCHASE">Purchase</a></li>
            <li><a href="#automated-purchase" title="Automated Purchase">Automated Purchase</a></li>
            <li><a href="#sale" title="SALE">Sale</a></li>
            <li><a href="#expense" title="EXPENSE">Expense</a></li>
            <li><a href="#quotation" title="QUOTATION">Quotation</a></li>
            <li><a href="#adjustment" title="QUANTITY ADJUSTMENT">Quantity Adjustment</a></li>
            <li><a href="#stock-count" title="STOCK COUNT">Stock Count</a></li>
            <li><a href="#transfer" title="TRANSFER">Transfer</a></li>
            <li><a href="#return" title="RETURN">Return</a></li>
            <li><a href="#accounting" title="ACCOUNTING">Accounting</a></li>
            <li><a href="#hrm" title="HRM">HRM</a></li>
            <li><a href="#people" title="PEOPLE">People</a></li>
            <li><a href="#reports" title="REPORTS">Reports</a></li>
            <li><a href="#dso-alert" title="Daily Sale Objective Alert">Daily Sale Objective Alert</a></li>
            <li><a href="#setting" title="SETTINGS">Settings</a></li>
            <li><a href="#translation" title="TRANSLATION">Translation</a></li>
            <li><a href="#video_tutorial" title="VIDEO TUTORIAL">Video Tutorial</a></li>
            <li><a href="#support" title="SUPPORT">Support</a></li>
        </ul>
    </div>
    <div id="documenter_content">
        <section id="documenter_cover">
            <h1>{{optional($general_setting)->site_title}}</h1>
            <div id="documenter_buttons">
            </div>
            <hr>
            <ul>
                <li>By: {{optional($general_setting)->developed_by}}</li>
            </ul>
            <p>{{optional($general_setting)->site_title}} is a software that will help you to manage your inventory, accounting and hrm. We believe that this software is suitable for both wholesale and retail buisness model and an ideal product for any Super Shop. This user friendly software is fully responsive and has many features. Hope that this software will be helpful to manage your buisness inventory.</p>
        </section>
        <section id="server_requirement">
            <div class="page-header">
                <h3>SERVER REQUIREMENT</h3>
                <hr class="notop">
            </div>
            <p>
                All our products are designed on most popular PHP framework Laravel. You need to have minimum requirement for running all our application. Please make sure that you have completed these requirements.</p>
            <ul>
                <li>Preferred Server - Apache/Nginx</li>
                <li><strong>PHP Version 8.0</strong></li>
                <li>OpenSSL PHP Extension&nbsp;</li>
                <li>PDO PHP Extension&nbsp;</li>
                <li>PHP Fileinfo Extension</li>
                <li>Mbstring PHP Extension&nbsp;</li>
                <li>Tokenizer PHP Extension&nbsp;</li>
                <li>Zip Archive PHP Extension&nbsp;</li>
                <li>Mod Rewrite Enabled</li>
            </ul>
            <p>
                &nbsp;</p>
            <p>
                <strong>Please note if you try to install the application on any other server say LiteSpeed or IIS, you may get undesirable result. We do not recommend you to use other server than Apache or Nginx. Also we do not provide support for installation in server other than Apache.</strong></p>
        </section>
        <section id="install">
            <div class="page-header">
                <h3>INSTALL</h3>
                <hr class="notop">
            </div>
            <p>
                <strong>Localhost</strong>
            </p>
            <p>If you are installing on localhost, please keep in mind that you’ll need Internet for installation. After installation, you can use {{optional($general_setting)->site_title}} without Internet. We suggest you use <strong>XAMPP</strong> for localhost. You can use other solutions like WAMP, MAMP etc. as well and installation process for all of them are same.</p><p>Create a folder inside htdocs folder inside your XAMPP installation. Now copy the zip folder you downloaded from Codecanyon to htdocs folder and unzip it there. Rename it to whatever you want. Say, you have named it- ‘my-folder’. Now start your XAMPP (or whatever you are using) and go to your browser and access {{optional($general_setting)->site_title}} from your browser like- localhost/my-folder/.</p>
            <p>While installing you may face 'max execution time error', as importing dtatabase takes a while. To solve this issue, please increase 'max_execution_time' and 'memory_limit' value in your php.ini file. set memory_limit=512M and max_execution_time=480. for reference- <a href="https://youtu.be/zvOC76TXOnA">How to edit php.ini file on localhost(XAMPP)</a></p>
            <p><strong>Online Hosting</strong></p>
            <p>Upload the zip folder you downloaded from Codecanyon to your hosting and unzip it. Please make sure you configure your web hosting’s settings, so that it shows hidden files and folders (for reference- <a href="https://youtu.be/zvOC76TXOnA">How to enable hidden files and folders on cpanel</a>) This is to ensure that if you copy/move the contents from the unzipped folder to any other location, you copy all the files including ‘.htaccess’, ‘.env’ files which are necessary for the proper functioning of the software. Now you can access the folder where you have {{optional($general_setting)->site_title}} from your browser.</p>
            <p>Now follow the installation process below.</p>
            <h2><strong>Step 1</strong></h2>
            <ul>
                <li>
                    Please read the license agreement before proceeding.
                </li>
                <li>
                    You need to accept and continue for going to the next step.
                </li>
            </ul>
            <img alt="" src="read_me/assets/images/step1.png">
            <h2><strong>Step 2</strong></h2>
            <ul>
                <li>
                    The system will automatically check for the server requirements. If all the requirements are fulfilled, You can proceed for further action.
                </li>
            </ul>
            <img alt="" src="read_me/assets/images/step2.png">
            <h2><strong>Step 3</strong></h2>
            <ul>
                <li>
                    You have to fill up the form with correct information.
                </li>
                <li>
                    Please input the purchase code.
                </li>
                <li>
                    Input your database host e.g(localhost).
                </li>
                <li>
                    Your database username e.g(root).
                </li>
                <li>
                    Your database password (if any).
                </li>
                <li>
                    The database name that you have already created.
                </li>
                <li>
                    Click on submit.
                </li>
            </ul>
            <img alt="" src="read_me/assets/images/step3.png">
            <h2><strong>Step 4</strong></h2>
            <ul>
                <li>Congratulations! You have successfully installed {{optional($general_setting)->site_title}}.<br>
                    Note : This automatic process should delete the install folder which is inside the project folder.your project folder contains 'install' folder, then please delete the folder.
                </li>
            </ul>
            <br>
            <h2>Installation Video</h2>
            <p>Watch is a video demonstrating the steps stated above. (you'll need internet connection to play the video)</p>
            <iframe width="560" height="315" src="https://www.youtube.com/embed/Fvi09MgBxUU" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            <br><br>
            <p>You can also watch this video on <a href="https://www.youtube.com/watch?v=Fvi09MgBxUU">youtube</a></p>

            <h2><strong>Help with installation</strong></h2>
            <p>We can help you install on any cpanel based hosting for as little as $15. You can send the money via paypal to tarik_17@yahoo.co.uk. Contact us at <a href="https://lion-coders.com/support">Support</a> with your hosting details and payment proof and we'll take care of the rest.</p>
            <p><strong>Please note</strong>- If you are installing on localhost, we'll charge $50 for anydesk/teamviewer support(installation).</p>
        </section>
        <section id="addon">
            <div class="page-header">
                <h3>Addons</h3>
                <hr class="notop">
            </div>
            <h2><strong>SalePro WooCommerce</strong></h2>
            <p>Imagine you have an existing WooCommerce website. Now you want to use salepro and also want to synchronize your WooCommerce website with SalePro. If you want to do so then this is the ultimate solution you are looking for. SalePro WooCommerce addon comes with complete synchronization with your WooCommerce website. You can synchronize your orders from WooCommerce to SalePro. Also you can can synchronize products, categories from SalePro to WooCommerce. And you have to map your Salepro taxes with WooCommerce taxes.</p>
            <p>To purchase this addon go to the addon list from left sidebar and click on the Buy Now button.</p>
            <p>
                <img alt="" src="read_me/assets/images/woocommerce1.png">
            </p>
            <p>After purchasing this addon from codecanyon click on the install button. Type your purchase key which you will get from the envato during the purchase. Then click on the submit button. If your purchase key is correct then the addon will be installed automatically and you will see a new option on the left side bar named WooCommerce.</p>
            <p>
                <img alt="" src="read_me/assets/images/woocommerce2.png">
                <img alt="" src="read_me/assets/images/woocommerce3.png">
            </p>
            <p>If you go to the WooCommerce section you will get all the necessary options to sync your Wocommerce categories, products, taxes and orders. You can also sync products from SalePro to WooCommerce from the product create or edit page.</p>
            <p>
                <img alt="" src="read_me/assets/images/woocommerce4.png">
            </p>
            <p>When we released the update you can update it automatically from the addon list page.</p>
            <p>
                <img alt="" src="read_me/assets/images/woocommerce5.png">
            </p>
            <h2><strong>SalePro eCommerce</strong></h2>
            <p>For SalePro eCommerce installation and documentation , please visit <a href="{{url('/ecommerce-documentation')}}">SalePro eCommerce Docs</a></p>
        </section>
        <section id="common-error">
            <div class="page-header">
                <h3>Common Errors</h3>
                <hr class="notop">
            </div>
            <p>If you face 500 server error after installing the software please update your php version to 8.2+. If you still get 500 error after updating php version, please open your '.env' file and change the value of 'APP_DEBUG' to true. You'll find '.env' file in the root folder ({{optional($general_setting)->site_title}}) And then go to the page again where you were getting 500 server error. You should see description of actual error now. Please take a screenshot and send it over along with your cpanel access details, so that we can look into it.</p>
            <img alt="" src="read_me/assets/images/env.png">
            <img alt="" src="read_me/assets/images/app_debug.png">
        </section>
        <section id="update">
            <div class="page-header">
                <h3>SOFTWARE UPDATE</h3>
                <hr class="notop">
            </div>
            <h2><strong>AUTOMATIC UPDATE</strong></h2>
            <p>We released automatic update feature in version 3.8.0. So if you want this feature your software version must be at least 3.8.0. Any admin/owner user will be notified on the dashboard when the update will be released.</p>
            <p>
                <img alt="" src="read_me/assets/images/update8.png">
            </p>
            <p>If your software version is less than 3.8.0 then follow the following procedure to update the software. Once you do it after that you will receive update notification on the dashboard as we described earlier.</p>
            <h2><strong>UPDATE with Existing Data</strong></h2>s
            <p>You can update the software very easily by following 4 steps:</p>
            <ul>
                <li>Rename your previous database like {{optional($general_setting)->site_title}}-backup.sql.</li>
                <li>Delete the project folder and reinstall it</li>
                <li>Merge your present database with previous one with <a href="https://www.red-gate.com/products/mysql/mysql-compare/index">MySQL Compare</a>. If you are a linux user you can use <a href="https://www.navicat.com/en/">Navicat</a> to merge database.</li>
                <li>After that delete the new database and rename the previous one with present database name like {{optional($general_setting)->site_title}}-backup.sql to {{optional($general_setting)->site_title}}.sql.</li>
            </ul>
            <p><strong>Please follow the following spanshots carefully to merge database:</strong></p>
            <p>Open the software.</p>
            <p>
                <img alt="" src="read_me/assets/images/update1.png">
            </p>
            <p>Select your source and target database and click compare now.</p>
            <p>
                <img alt="" src="read_me/assets/images/update2.png">
            </p>
            <p>After comparing successfully two database click ok.</p>
            <p>
                <img alt="" src="read_me/assets/images/update3.png">
            </p>
            <p>Then select the checkbox and click Diployment Wizard.</p>
            <p>
                <img alt="" src="read_me/assets/images/update4.png">
            </p>
            <p>Uncheck the Recompare after deployment checkbox and click next.</p>
            <p>
                <img alt="" src="read_me/assets/images/update5.png">
            </p>
            <p>Click Deploy now.</p>
            <p>
                <img alt="" src="read_me/assets/images/update6.png">
            </p>
            <p>Click Ok.</p>
            <p>
                <img alt="" src="read_me/assets/images/update7.png">
            </p>
            <p>Thats all! You have just updated the database. Now follow step 4 as we described earlier.</p>
            <h2><strong>UPDATE without Existing Data</strong></h2>
            <p>You can update the software very easily by following 2 steps:</p>
            <ul>
                <li>Delete your previous database it.</li>
                <li>Delete the project folder and reinstall it</li>
            </ul>
            <p><strong>Still facing problem? Don't worry! We can update your software for USD 15. Please contact us at <a href="https://lion-coders.com/support">Support</a>.</strong></p>
        </section>
        <section id="pos-printer">
            <div class="page-header">
                <h3>POS Printer Configuration</h3>
                <hr class="notop">
            </div>
            <p>
                First you have to install your printer driver. Then go to settings and select Devices.
            </p>
            <p>
                <img alt="" src="read_me/assets/images/pos_printer1.png">
            </p>
            <p>
                Then go to Devices and printers.
            </p>
            <p>
                <img alt="" src="read_me/assets/images/pos_printer2.png">
            </p>
            <p>
                Set your POS printer as default printer.
            </p>
            <p>
                <img alt="" src="read_me/assets/images/pos_printer3.png">
            </p>
            <p>
                Then go to Printing preferences.
            </p>
            <p>
                <img alt="" src="read_me/assets/images/pos_printer4.png">
            </p>
            <p>
                Then go to Advanced.
            </p>
            <p>
                <img alt="" src="read_me/assets/images/pos_printer5.png">
            </p>
            <p>
                Select 3rd option of paper size and click Ok.
            </p>
            <p>
                <img alt="" src="read_me/assets/images/pos_printer6.png">
            </p>
            <p>
                After that go to printer properties.
            </p>
            <p>
                <img alt="" src="read_me/assets/images/pos_printer7.png">
            </p>
            <p>
                Go to device settings and select 3rd option of auto.
            </p>
            <p>
                <img alt="" src="read_me/assets/images/pos_printer8.png">
            </p>
            <p>
                Please make sure you choose correct paper size(3rd option) when you want to print the invoice.
            </p>
            <p>
                <img alt="" src="read_me/assets/images/pos_printer9.png">
            </p>
        </section>
        <section id="empty-db">
            <div class="page-header">
                <h3>Empty Database</h3>
                <hr class="notop">
            </div>
            <p>
                When you install this software it will come with some dummy data. To delete all these dummy data click on your username on the top navigation bar. Then you will see an option named Empty Database. Click on that and all the dummy data will be deleted automatically.
            </p>
            <p>
                <img alt="" src="read_me/assets/images/empty_database1.png">
            </p>
            <p>
                <img alt="" src="read_me/assets/images/empty_database2.png">
            </p>
        </section>
        <section id="mail">
            <div class="page-header">
                <h3>SETUP MAIL SERVER</h3>
                <hr class="notop">
            </div>
            <p>
                To add mail functionality to your inventory you have to setup mail server first. To do this go to <strong>Mail Setting</strong> under <strong>Setting</strong> module. You have to fill up the following information.
            </p>
            <p>
                <img alt="" src="read_me/assets/images/mail1.png">
            </p>
        </section>
        <section id="dashboard">
            <div class="page-header">
                <h3>DASHBOARD</h3>
                <hr class="notop">
            </div>
            <p>
                We have a gorgeous looking dashboard for our customer from where they get Revenue, Sale Return, Purchase Return and Profit information of today / last 7 days / current month / current year at a glance by one click.
            </p>
            <p>
                <img alt="" src="read_me/assets/images/dashboard1.png">
            </p>
            <p>You will get information of your cash flow that means how much money you are earning and how much money you are spending from this line chart.</p>
            <p>
                <img alt="" src="read_me/assets/images/dashboard4.png">
            </p>
            <p>You also aware of your current month's <strong>purchase</strong>, <strong>revenue</strong> <strong>expenditure</strong> froms this doughnut chart.</p>
            <p>
                <img alt="" src="read_me/assets/images/dashboard5.png">
            </p>
            <p>A bar chart shows Yearly report of purchases and sales of current year.</p>
            <p><img alt="" src="read_me/assets/images/dashboard2.png"></p>
            <p>From <strong>Dashboard</strong> You will also get recent transaction(sale, purchase, quotation, payment) and top 5 best selling product of current month and current year.
            </p>
            <p><img alt="" src="read_me/assets/images/dashboard3.png"></p>
        </section>
        <section id="product">
            <div class="page-header">
                <h3>PRODUCT</h3>
                <hr class="notop">
            </div>
            <h2><strong>Category</strong></h2>
            <p>You can add, edit and delete product category. You can also import category from CSV file and export table data to PDF, Excel, CSV. Also you can print data from table.</p>
            <p>
                <img alt="" src="read_me/assets/images/category1.png">&nbsp;&nbsp;
                <img alt="" src="read_me/assets/images/category2.png">
            </p>
            <p>
                <img alt="" src="read_me/assets/images/category3.png">&nbsp;&nbsp;
                <img alt="" src="read_me/assets/images/category4.png">
            </p>
            <p>
                If you dont want to export any column you can do this by clicking Column Visibility button. From here you can choose column to remove from table.
            </p>
            <p>
                <img alt="" src="read_me/assets/images/category5.png">&nbsp;&nbsp;
                <img alt="" src="read_me/assets/images/category6.png">
            </p>
            <p>To export data from specific row you just have to check the checkbox of the related row</p>
            <p>
                <img alt="" src="read_me/assets/images/category9.png">
            </p>
            <p>If you want to delete all the row from table you can do this very easily as shown below. You can also delete specific row from table.</p>
            <p>
                <img alt="" src="read_me/assets/images/category10.png">
            </p>
            <p>
                If you want to search anything from the table you can simply type the word in the search box.
            </p>
            <p>
                <img alt="" src="read_me/assets/images/category7.png">
            </p>
            <p>
                You can also control the pagination from <strong>Show</strong> dropdown.
            </p>
            <p>
                <img alt="" src="read_me/assets/images/category8.png">
            </p>
            <h2><strong>Product</strong></h2>
            <p>In product section you will just add general information of a product. <strong>To add stock you have to purchase that product.</strong> You can create three types of product in {{optional($general_setting)->site_title}}.</p>
            <ul>
                <li>Standard</li>
                <li>Digital</li>
                <li>Combo (Combination of standard product. Like mango juice is a combo product as it is consist of mango and sugar ).</li>
                <li>Service</li>
            </ul>
            <p>You can add, edit and delete product. You can import product from CSV. <strong>You must follow the instruction to import data from CSV</strong>. To get better understanding you can download the sample file.</p>
            <p>
                <img alt="" src="read_me/assets/images/product1.png">&nbsp;&nbsp;
                <img alt="" src="read_me/assets/images/product2.png">
            </p>
            <p>You can sort table data according to column</p>
            <p><img alt="" src="read_me/assets/images/product3.png"></p>
            <p>And you can search, export and print data from table that we discussed earlier in greater detail.</p>
        </section>
        <section id="weight-scale-machine">
            <div class="page-header">
                <h3>Weight Scale Machine</h3>
                <hr class="notop">
            </div>
            <p>{{optional($general_setting)->site_title}} is compatible with weight scale machine. But you have to follow the rules strictly. In a weight scale machine the machine will give you a label of 13 digits barcode. Consider the following picture: </p>
            <p><img alt="" src="read_me/assets/images/weight_machine1.jpeg"></p>
            <p>Here first 7 digits are the product code which must be similar with the product code of {{optional($general_setting)->site_title}}. Next 5 digits contains weight and the last digit is a random number. Whenever you put a product on a weight scale machine it will generate this type of label automatically. Then all you have to do is scan this label. Product's info and weight will be calculated automatically with {{optional($general_setting)->site_title}}. But the product code must be same both for {{optional($general_setting)->site_title}} and weight scale machine.</p>
            <p><img alt="" src="read_me/assets/images/weight_machine2.jpeg"></p>
        </section>
        <section id="print-barcode">
            <div class="page-header">
                <h3>Print Barcode</h3>
                <hr class="notop">
            </div>
            <P>You can print barcode with {{optional($general_setting)->site_title}}. The available paper sizes are 36mm, 24mm and 18mm. Anything other than that might not work correctly. To print barcodes we highly recommend using <strong>Brother Label Printer.</strong></P>
        </section>
        <section id="adding-stock">
            <div class="page-header">
                <h3>ADDING STOCK</h3>
                <hr class="notop">
            </div>
            <P>In <strong>Product</strong> section you just added general information of product. So where the stock comes from? To add stock you have to purchase that product for specific warehouse. This software is pretty smart that it will automatically update the stock quantity and you don't have to worry about it.</P>
        </section>
        <section id="purchase">
            <div class="page-header">
                <h3>PURCHASE</h3>
                <hr class="notop">
            </div>
            <h2><strong>Add Purchase</strong></h2>
            <p>You can create purchase in Purchase module. <strong>By creating purchase the stock quantity of product will be increased.</strong> .There are three purchase status: Recieved, Partial, Pending, Orderd. You can add product to order table by typing or scanning barcode of product</p>
            <p>
                <img alt="" src="read_me/assets/images/purchase1.png">&nbsp;&nbsp;
                <img alt="" src="read_me/assets/images/purchase2.png">
            </p>
            <p>You can also edit product info from order table.</p>
            <p><img alt="" src="read_me/assets/images/purchase3.png"></p>
            <p>After creating purchase you will be redirected to purchase index page. You will get summary of purchase from table. To get details you just have to click in the table row.</p>
            <p><img alt="" src="read_me/assets/images/purchase4.png"></p>
            <h2><strong>Import Purchase</strong></h2>
            <p>You can import sale from CSV.<strong>You must follow the instruction to import data from CSV</strong>. To get better understanding you can download the sample file. </p>
            <p><img alt="" src="read_me/assets/images/purchase7.png"></p>
            <h2><strong>Payment</strong></h2>
            <p>You can make payment from Purchase table. You can make payment with Cash, Gift Card, Cheque, Credit card and Deposit.</p>
            <p><img alt="" src="read_me/assets/images/purchase5.png">&nbsp;&nbsp;
                <img alt="" src="read_me/assets/images/purchase6.png">
            </p>
            <p>And you can search, export and print data from table that we discussed in <a href="#product">Product</a> section.</p>
        </section>
        <section id="automated-purchase">
            <div class="page-header">
                <h3>Automated Purchase</h3>
                <hr class="notop">
            </div>
            <p>User can make the purchase automated for those products which quantity exceeds the alert quantity by setting up the cron job. Please follow the following steps to make it workable.</p>
            <p>First go to the cpanel and go to the cron job settings</p>
            <p><img alt="" src="read_me/assets/images/automated_purchase1.png"></p>
            <p>Then set up the cron job as described below. Here cron job is set for every 5 minitues. So the system will check in every five minitues and if any product exceeds the alert quantity an auotomated purchase will be made.</p>
            <p><img alt="" src="read_me/assets/images/automated_purchase2.png"></p>
            <p><strong>Note:</strong> If you face any issues while setting up cron jobs, please contact at <a href="https://lion-coders.com/support">Support</a>.</p>
        </section>
        <section id="sale">
            <div class="page-header">
                <h3>SALE</h3>
                <hr class="notop">
            </div>
            <h2><strong>POS</strong></h2>
            <p>You can create sale from POS. Customer, Warehouse and Biller (representative of your company) will be automatically selected according to POS Settings under <a href="#setting">Settings</a> module. Touch screen keybord is activated in POS module. You can add product to order table by typing or scanning barcode of product. Featured Product will be displayed in the right side. You can also add product by clicking product image. You can edit product info from order table.</p>
            <p>
                <img alt="" src="read_me/assets/images/sale1.png">
                <img alt="" src="read_me/assets/images/sale2.png">&nbsp;&nbsp;
                <img alt="" src="read_me/assets/images/sale3.png">
            </p>
            <p>To add order discount, order tax and shipping cost you just have to click the button that are shown below. To finalize the sale you have to click the <strong>Payment</strong> button.</p>
            <p><img alt="" src="read_me/assets/images/sale4.png"></p>
            <p>After creating sale you will be redirected to sale index page. A confirmation mail will be sent automatically to customer's email with sale details. You will get summary of sale from table. To get details you just have to click in the table row.</p>
            <p>You can also generate <strong>Invoice</strong> automatically which is beutifully designed</p>
            <p><img alt="" src="read_me/assets/images/sale6.png"></p>
            <p>You can also create sale by clicking Add Sale button. Also you can import sale from CSV.<strong>You must follow the instruction to import data from CSV</strong>. To get better understanding you can download the sample file. </p>
            <p><img alt="" src="read_me/assets/images/sale5.png"></p>
            <h2><strong>Payment</strong></h2>
            <p>You can make payment from Sale table. You can make payment with Cash, Cheque, Credit Card, Gift Card, Deposit and Paypal. A confirmation mail will be sent automatically to customer's email with payment details.</p>
            <p><img alt="" src="read_me/assets/images/purchase5.png">&nbsp;&nbsp;
                <img alt="" src="read_me/assets/images/purchase6.png">
            </p>
            <p>And you can search, export and print data from table that we discussed in <a href="#product">Product</a> section.</p>
            <h2><strong>Delivery</strong></h2>
            <p>You can add delivery for your sold products. A confirmation mail will be sent automatically to customer's email with delivery details.</p>
            <p><img alt="" src="read_me/assets/images/delivery1.png"></p>
            <p>And you can search, export and print data from table that we discussed in <a href="#product">Product</a> section.</p>
            <h2><strong>Gift Card</strong></h2>
            <p>You can sell GiftCard to customer. By using gift card customer can purchase product. Again GiftCard can be recharged. Customer will be notified by mail when assigning or recharging a GiftCard.</p>
            <p>
                <img alt="" src="read_me/assets/images/gift_card1.png">&nbsp;&nbsp;
                <img alt="" src="read_me/assets/images/gift_card2.png">
            </p>
        </section>
        <section id="expense">
            <div class="page-header">
                <h3>EXPENSE</h3>
                <hr class="notop">
            </div>
            <h2><strong>Expense Category</strong></h2>
            <p>You can create, edit and delete expense category in Expense module.</p>
            <p>
                <img alt="" src="read_me/assets/images/expense1.png">
            </p>
            <h2><strong>Expense</strong></h2>
            <p>You can create, edit and delete expense in Expense module.</p>
            <p>
                <img alt="" src="read_me/assets/images/expense2.png">
            </p>
            <p>And you can search, export and print data from table that we discussed in <a href="#product">Product</a> section.</p>
        </section>
        <section id="quotation">
            <div class="page-header">
                <h3>QUOTATION</h3>
                <hr class="notop">
            </div>
            <h2><strong>Add Quotation</strong></h2>
            <p>You can create quotation in Quotation module. There are two quotation status: Pending and Sent</p>
            <p>
                <img alt="" src="read_me/assets/images/quotation1.png">
            </p>
            <p>If quotation status is <strong>Sent</strong> a confirmation mail will be sent automatically to customer's email with quotation details.</p>
            <h2><strong>Create Sale</strong></h2>
            <p>You can create sale from Quotation.</p>
            <p><img alt="" src="read_me/assets/images/quotation2.png">
            </p>
            <h2><strong>Create Purchase</strong></h2>
            <p>You can create purchase from Quotation.</p>
            <p><img alt="" src="read_me/assets/images/quotation3.png">
            </p>
            <p>And you can search, export and print data from table that we discussed in <a href="#product">Product</a> section.</p>
        </section>
        <section id="adjustment">
            <div class="page-header">
                <h3>QUANTITY ADJUSTMENT</h3>
                <hr class="notop">
            </div>
            <h2><strong>Add Adjustment</strong></h2>
            <p>You can adjust product quantity in Quantity Adjustment module. There will be two operation: Subtraction and Addition</p>
            <p>
                <img alt="" src="read_me/assets/images/adjustment1.png">
                <img alt="" src="read_me/assets/images/adjustment2.png">
            </p>
            <p>And you can search, export and print data from table that we discussed in <a href="#product">Product</a> section.</p>
        </section>
        <section id="stock-count">
            <div class="page-header">
                <h3>STOCK COUNT</h3>
                <hr class="notop">
            </div>
            <p>You can count your stock from this module. Two types are available: <strong>Full</strong> and <strong>Partial</strong>. In Partial type user have to specify brand and category and the software will automatically count the stock for that brand or category. Then this information will be written in CSV file which you have to download to finalize the stock count. Please follow the instruction properly. After finalizing the stock count you can automatically adjust the quantity of products if it is necessary.</p>
        </section>
        <section id="transfer">
            <div class="page-header">
                <h3>TRANSFER</h3>
                <hr class="notop">
            </div>
            <h2><strong>Add Transfer</strong></h2>
            <p>You can transfer your product from one warehouse to another in Transfer module. You can also transfer product with CSV file. <strong>You must follow the instruction to import data from CSV.</strong> To get better understanding you can download the sample file. You will get details of transfer by clicking in the table row.</p>
            <p>
                <img alt="" src="read_me/assets/images/transfer1.png">
            </p>
            <p>And you can search, export and print data from table that we discussed in <a href="#product">Product</a> section.</p>
        </section>
        <section id="return">
            <div class="page-header">
                <h3>RETURN</h3>
                <hr class="notop">
            </div>
            <h2><strong>Add Return</strong></h2>
            <p>You can return your product with Return module. You can track return of both purchase and sale with this module. A confirmation mail will be sent automatically to customer's email with return details if customer refund products. Again if you return product to supplier a confirmation mail will be sent automatically to supplier's email with return details. You will get details of return by clicking in the table row.</p>
            <p>
                <img alt="" src="read_me/assets/images/return1.png">
            </p>
            <p>And you can search, export and print data from table that we discussed in <a href="#product">Product</a> section.</p>
        </section>
        <section id="accounting">
            <div class="page-header">
                <h3>Accounting</h3>
                <hr class="notop">
            </div>
            <h2><strong>Account</strong></h2>
            <p>You can create,edit and delete account to link all your transactions. You can also set default account for sale. All the payments must be done under an account.</p>
            <p>
                <img alt="" src="read_me/assets/images/accounting_1.png">
            </p>
            <p>You can generate <strong>Balance Sheet</strong> of your accounts. You can also make <strong>Account Statement</strong> of an specific account to see all the transactions which has done with this account.</p>
        </section>
        <section id="hrm">
            <div class="page-header">
                <h3>HRM</h3>
                <hr class="notop">
            </div>
            <h2><strong>Department</strong></h2>
            <p>You can create,edit and delete department of your company.</p>
            <h2><strong>Employee</strong></h2>
            <p>You can create,edit and delete employee of your company. You can also give user access to employee.</p>
            <h2><strong>Attendance</strong></h2>
            <p>You can take employee attendance with this software. You can set CheckIn and CheckOut time in HRM Setting option under Setting Module.</p>
            <h2><strong>Payroll</strong></h2>
            <p>You can make payroll of your employee with this software. All payroll must be done from an specipic account.</p>
        </section>
        <section id="people">
            <div class="page-header">
                <h3>PEOPLE</h3>
                <hr class="notop">
            </div>
            <h2><strong>Add User</strong></h2>
            <p>You can create, edit and delete user account. By creating user account password will be sent to the user's email that is given. Again you can active or inactive a user.</p>
            <p>There is also be a register option to create user account. But his/her ID will not be activated untill admin will approve it.</p>
            <p>
                <img alt="" src="read_me/assets/images/user1.png">
            </p>
            <h2><strong>Add Customer</strong></h2>
            <p>You can create, edit and delete customer. After creating customer a confirmation email will automatically send to customer. You can add money to customer's database just like a bank account. You can also import customer with CSV file. <strong>You must follow the instruction to import data from CSV.</strong></p>
            <p>
                <img alt="" src="read_me/assets/images/customer1.png">
            </p>
            <p>
                <img alt="" src="read_me/assets/images/customer2.png">
            </p>
            <h2><strong>Add Biller</strong></h2>
            <p>Biller is the representative of your company. You may have multiple company and you want to manage all your inventory from a single platform. So this is a solution for enterprise. You can create, edit and delete biller. After creating biller a confirmation email will automatically send to biller. You can also import biller with CSV file. <strong>You must follow the instruction to import data from CSV.</strong></p>
            <p>
                <img alt="" src="read_me/assets/images/biller1.png">
            </p>
            <h2><strong>Add Supplier</strong></h2>
            <p>Supplier is the people from whom you purchase products. You can create, edit and delete supplier. After creating supplier a confirmation email will automatically send to supplier. You can also import supplier with CSV file. <strong>You must follow the instruction to import data from CSV.</strong></p>
            <p>
                <img alt="" src="read_me/assets/images/supplier1.png">
            </p>
            <p>And you can search, export and print data from table that we discussed in <a href="#product">Product</a> section.</p>
        </section>
        <section id="reports">
            <div class="page-header">
                <h3>Reports</h3>
                <hr class="notop">
            </div>
            <p>You can create generate various reports automatically by using {{optional($general_setting)->site_title}}.</p>
            <ul>
                <li><strong>Profit / Loss Report</strong></li>
                <li><strong>Best Seller Report</strong></li>
                <li><strong>Product Report</strong></li>
                <li><strong>Daily Sale Report</strong></li>
                <li><strong>Monthly Sale Report</strong></li>
                <li><strong>Daily Purchase Report</strong></li>
                <li><strong>Monthly Purchase Report</strong></li>
                <li><strong>Sale Report</strong></li>
                <li><strong>Sale Report Chart</strong></li>
                <li><strong>Payment Report</strong></li>
                <li><strong>Purchase Report</strong></li>
                <li><strong>Warehouse Stock Chart Report</strong></li>
                <li><strong>Product Quantity Alert Report</strong></li>
                <li><strong>Daily Sale Objective Report</strong></li>
                <li><strong>User Report</strong></li>
                <li><strong>Customer Report</strong></li>
                <li><strong>Supplier Report</strong></li>
                <li><strong>Due Report</strong></li>
            </ul>
        </section>
        <section id="dso-alert">
            <div class="page-header">
                <h3>Daily Sale Objective Alert</h3>
                <hr class="notop">
            </div>
            <p>You can set daily sale objective for specific products. Meaning the minumum sold quantity for a product in a day. If the product can not fulfill the objective user will be notified on the dashboard. To make this feature automated you have to set up the cron job correctly.</p>
            <p>First go to the cpanel and go to the cron job settings</p>
            <p><img alt="" src="read_me/assets/images/automated_purchase1.png"></p>
            <p>Then set up the cron job as described below. Here cron job is set up for once per day. So the system will check at 12:00 am if any product can not fulfill the daily sale objective for previous day. Products those can not fulfill the daily sale objective will be listed to the daily sale objective report.</p>
            <p><img alt="" src="read_me/assets/images/dso_alert1.png"></p>
            <p><strong>Note:</strong> If you face any issues while setting up cron jobs, please contact at <a href="https://lion-coders.com/support">Support</a>.</p>
        </section>
        <section id="setting">
            <div class="page-header">
                <h3>SETTINGS</h3>
                <hr class="notop">
            </div>
            <h2><strong>Add Role</strong></h2>
            <p>You can create, edit and delete user roles. You can controll user access by changing the role permission. So, under a certain role users have specific access over this software</p>
            <p>
                <img alt="" src="read_me/assets/images/role1.png">
            </p>
            <h2><strong>Add Warehouse</strong></h2>
            <p>You can create, edit and delete warehouse. You can also import warehouse with CSV file. <strong>You must follow the instruction to import data from CSV.</strong></p>
            <p>
                <img alt="" src="read_me/assets/images/warehouse1.png">
            </p>
            <h2><strong>Add Customer Group</strong></h2>
            <p>
                You can create, edit and delete customer group. Different customer group has different price over the product. You can modify this by changing price percentage in Customer Group module.
            </p>
            <p>
                You can also import customer group with CSV file. <strong>You must follow the instruction to import data from CSV.</strong>
            </p>
            <p>
                <img alt="" src="read_me/assets/images/customer_group1.png">
            </p>
            <h2><strong>Add Brand</strong></h2>
            <p>You can create, edit and delete product brand. You can also import brand with CSV file. <strong>You must follow the instruction to import data from CSV.</strong></p>
            <p>
                <img alt="" src="read_me/assets/images/brand1.png">
            </p>
            <h2><strong>Add Unit</strong></h2>
            <p>You can create, edit and delete product unit. You can also import brand with CSV file. <strong>You must follow the instruction to import data from CSV.</strong></p>
            <p>
                <img alt="" src="read_me/assets/images/unit1.png">
            </p>
            <h2><strong>Add Tax</strong></h2>
            <p>You can create, edit and delete different product tax. You can also import tax with CSV file. <strong>You must follow the instruction to import data from CSV.</strong></p>
            <p>
                <img alt="" src="read_me/assets/images/tax1.png">
            </p>
            <p>And you can search, export and print data from table that we discussed in <a href="#product">Product</a> section.</p>
            <h2><strong>General Settings</strong></h2>
            <p>You can change Site Title, Site Logo, Currency, Time Zone, Staff Access, Date Format and Theme Color from general settings</p>
            <h2><strong>User Profile</strong></h2>
            <p>You can update user profile info from this module</p>
            <h2><strong>POS Settings</strong></h2>
            <p>You can set your own POS settings from this module. You can set default customer, biller, warehouse and how many Featured products will be displayed in the POS module. You have to set your <strong>Stripe</strong> public and private key for Credit Card Payment. To implement payment with <strong>Paypal</strong> you have to buy live api from Paypal. You will also need to fillup the following information.
            </p>
            <p>
                <img alt="" src="read_me/assets/images/pos1.png">
            </p>
            <h2><strong>HRM Setting</strong></h2>
            <p>You can set default CheckIn and CheckOut time in HRM Setting.</p>
            <h2><strong>SMS Setting</strong></h2>
            <p>You can use Bulk SMS service via <strong>Twilio</strong> and <strong>Clickatell</strong>. You just have to fill the information correctly to activate this service. <strong>Please provide country code to send sms.</strong></p>
        </section>
        <section id="translation">
            <div class="page-header">
                <h3>TRANSLATION</h3>
                <hr class="notop">
            </div>
            <p>Right now this software is supported in 17 languages.</p>
            <ul>
                <li>English</li>
                <li>Spanish</li>
                <li>French</li>
                <li>Arabic</li>
                <li>Simplified Chinese</li>
                <li>Traditional Chinese</li>
                <li>Portugeese</li>
                <li>German</li>
                <li>Dutch</li>
                <li>Malay</li>
                <li>Hindi</li>
                <li>Vietnamese</li>
                <li>Italian</li>
                <li>Russian</li>
                <li>Bulgarian</li>
                <li>Turkish</li>
                <li>Lao</li>
            </ul>
            We hope that in future this software will be supported in more other languages. You can convert this software in your preferable language by simply changing the language option.</p>
            <p>
                <img alt="" src="read_me/assets/images/translation.png">
            </p>
            <p>If you are not satisfied with our translation go to resources/lang and open your desired language folder and edit the file.php.</p>
            <p>Special thanks to <strong>Dhiman Barua</strong> who made these translation files for our respected customers.</p>
        </section>
        <section id="video_tutorial">
            <div class="page-header">
                <h3>VIDEO TUTORIAL</h3>
                <hr class="notop">
            </div>
            <p>
                <iframe width="560" height="315" src="https://www.youtube.com/embed/videoseries?list=PLsh7QWvPhxo4_hu-i3B-0VEgy7oGM0ReH" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </section>
        <section id="support">
            <div class="page-header">
                <h3>SUPPORT</h3>
                <hr class="notop">
            </div>
            <p>We are happy to provide support for any issues within our software. We also provide customization service for as little as $15/hour. So if you have any features in mind or suugestions, please feel free to contact us at <a href="https://lion-coders.com/support"><strong>Support</strong></a>. Please note that we don't provide support though any other means (example- whatsapp, comments etc.). So, please refrain from commenting your queries on codecanyon or kocking us elsewhere.</p>
            <p>Also, in case of any errors/bugs/issues on your installation, please contact us with your hosting details (url, username, password), software admin access (url, username, password) and purchase code. If your support period has expired, please renew support on codecanyon before contacting us for support.</p>
            <p>Thank you and  best wishes from {{optional($general_setting)->developed_by}}</p>
        </section>
    </div>
    <script type="text/javascript">
    	$("#documenter_sidebar").mCustomScrollbar({
            theme: "light",
            scrollInertia: 200
        });
    </script>
</body>

</html>
