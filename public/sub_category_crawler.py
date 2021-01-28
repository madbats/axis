#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Created on Wed Aug 28 20:44:27 2020
This script enables to get all current conferences at site http://portal.core.edu.au/conf-ranks/
Each conference consists of name, acronym, category code, category name
@author: 
"""
import re
import requests 
from bs4 import BeautifulSoup
import json

pager_num = 1

data = {}
    
data['conference'] = []

for i in range(1, 19):
    
    url = "http://portal.core.edu.au/conf-ranks/?search=&by=all&source=CORE2020&sort=atitle&page=" + str(pager_num)
    
    page = requests.get(url)
    
    soup = BeautifulSoup(page.text, 'lxml')
    
    table = soup.find_all('table')[0] # Grab the first table
    
    conf_num = 0
    
    for row in table.find_all('tr'):
        
         columns = row.find_all('td')
         
         extract_row = {}
         
         # get data row
         if (len(columns) != 0):
             conf_name = str(columns[0].contents[0])
             conf_name = re.sub(r'[^\w]', ' ', conf_name).strip() # remove noise characters
             extract_row['conf_name'] = conf_name;
             
             conf_acronym = str(columns[1].contents[0])
             conf_acronym = re.sub(r'[^\w]', ' ', conf_acronym).strip()
             extract_row['conf_acronym'] = conf_acronym;      
         
         # access conf page
         str_url = str(row.get('onclick'));
         
         #print(str_url)
         
         conf_url = str_url[len('navigate(\')') : len(str_url) - len('\')')]
         
         conf_url = 'http://portal.core.edu.au/' + conf_url
         
         # get category data
         conf_page = requests.get(conf_url)
         
         conf_soup = BeautifulSoup(conf_page.text, 'lxml')
         
         categories = conf_soup.find_all(string=re.compile("Primary Field Of Research:"))
         
         if len(categories) != 0:
             str_category = str(categories[0])
             # [0]: code - [1] : category name
             category_fields = str_category[len('Primary Field Of Research: '):].split(" - ")
             extract_row['cat_code'] = category_fields[0]
             extract_row['cat_name'] = category_fields[1]
         data['conference'].append(extract_row)
         #print(extract_row)
with open("data.json", "w") as outfile:
    json.dump(data, outfile)
    
print(json.dumps(data))