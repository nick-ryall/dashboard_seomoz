# SEOmoz Dashboard Panel
 
* Version: 1.0
* Author: Nick Ryall
* Build Date: 2011-08-24
* Requirements: Symphony 2.2, Dashboard extension

## Purpose

Provides SEO related info, like inbound links, retrieved using the SEOmoz Linkscape API.

## Installation
 
1. Upload the 'dashboard_seomoz' folder in this archive to your Symphony 'extensions' folder
2. Enable it by selecting "Dashboard SEOmoz" in the list, choose Enable from the with-selected menu, then click Apply
3. Navigate to the Dashboard from the "Dashboard" link in the primary navigation and select "Dashboard SEOmoz" from the "Create New" list

## Usage

You will need to enter the following information when creating a new panel:

* Domain
* Access ID
* Secret Key

If you have an SEOmoz.org account, you can log in and find your credentials on the 'http://www.seomoz.org/api' page. If you don't have a free SEOmoz.org account, sign up, and visit the API page to retrieve your API credentials.

Please note that your details will be stored as plain text.

## Change Log

* Changed from APC to native Symphony caching. 