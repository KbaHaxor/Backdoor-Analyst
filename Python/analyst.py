#!usr/bin/env python

import time
import os,sys
import urllib2, urllib
import glob
from termcolor import colored
from tqdm import tqdm
import datetime
import base64


PANEL_URL = "http://localhost:8888/analyst/"
GATE_URL = "logs.php"

SUSPECT = 0
SUSPECT_AD = 0
SUSPECT_LINK = []
SUSPECT_WORD = ['shell_exec(','eval(']
SUSPECT_ADVANCED  = []

class bcolors:
    HEADER = '\033[95m'
    OKBLUE = '\033[94m'
    OKGREEN = '\033[92m'
    WARNING = '\033[93m'
    FAIL = '\033[91m'
    ENDC = '\033[0m'

    def disable(self):
        self.HEADER = ''
        self.OKBLUE = ''
        self.OKGREEN = ''
        self.WARNING = ''
        self.FAIL = ''
        self.ENDC = ''

def advanced(link, word):
    global SUSPECT_WORD
    global SUSPECT_ADVANCED
    global SUSPECT_AD
    last_modified = time.ctime(os.path.getmtime(link))
    last = last_modified.split(' ')[4]
    if len(last) > 4:
        last = last_modified.split(' ')[5]
    date_creation = time.ctime(os.path.getctime(link))
    create = date_creation.split(' ')[4]
#    if create < last:
#        print "[ creation date: %s ]" % date_creation
#        if date_creation != last_modified:
#            print (bcolors.OKBLUE + "[ last modified: %s ]" + bcolors.ENDC) % last_modified
#            return True
#        else:
#            print "[ last modified: %s ]" % last_modified
    with open(link, 'r') as line:
        data = line.read()
        for element in SUSPECT_WORD:
            if element in data:
                if element != word:
                    SUSPECT_ADVANCED.append(link+":"+element)
                    SUSPECT_AD += 1
                    return True

def analyst_adv(path, word):
    global PANEL_URL
    global GATE_URL
    global SUSPECT_WORD
    global SUSPECT_ADVANCED
    global SUSPECT_AD
    with open(path, 'r') as line:
        grep = ""
        data = line.read()
        for element in SUSPECT_WORD:
            if element in data:
                if element != word:
                    level = "Dangerous"
                    for line in open(path):
                        if element in line:
                            grep = base64.b64encode(line)
                    #print(PANEL_URL + GATE_URL + "?app=" + path + "&level=" + level + "&grep="+grep+"&word2="+element)
                    data_posts = {'app': path,'level' : level,'grep':grep,'word2':element}
                    data_post = urllib.urlencode(data_posts)
                    req = urllib2.Request(PANEL_URL + GATE_URL, data_post)
                    response = urllib2.urlopen(req)
                    #urllib2.urlopen(PANEL_URL + GATE_URL + "?app=" + path + "&level=" + level + "&grep="+grep+"&word2="+element)

def add_panel(file_link):
    global PANEL_URL
    global GATE_URL
    global SUSPECT
    global SUSPECT_LINK
    global SUSPECT_WORD
    ok = "0"
    with open(file_link, 'r') as line:
        data = line.read()
        for element in SUSPECT_WORD:
            if element in data:
                ok = "1"
                if file_link not in SUSPECT_LINK:
                    level = "suspicious"
                    for line in open(file_link):
                        if element in line:
                            grep = base64.b64encode(line)
                    source = base64.b64encode(data)
                    data_posts = {'app': file_link,'level' : level,'grep':grep,'word':element,'source':source}
                    data_post = urllib.urlencode(data_posts)
                    req = urllib2.Request(PANEL_URL + GATE_URL, data_post)
                    response = urllib2.urlopen(req)
                    analyst_adv(file_link, element)
    if ok == "0":
        level = "basic"
        data_posts = {'app': file_link,'level' : level,'grep':'none'}
        data_post = urllib.urlencode(data_posts)
        req = urllib2.Request(PANEL_URL + GATE_URL, data_post)
        response = urllib2.urlopen(req)
        #urllib2.urlopen(PANEL_URL + GATE_URL + "?app=" + file_link + "&level=" + level + "&grep=none")

def listen_analyst(path):
        file_txt = glob.glob(path + "/*")
        for element in file_txt:
            if os.path.isfile(element):
                add_panel(element)
            else:
                listen_analyst(element)
            
def stats():
    global SUSPECT_LINK
    global SUSPECT
    global SUSPECT_ADVANCED
    global SUSPECT_AD
    print(bcolors.OKGREEN + "--------------------" + bcolors.ENDC)
    print("# BASIC " + str(SUSPECT) + " #")
    print(bcolors.OKGREEN + "--------------------" + bcolors.ENDC)
    if SUSPECT > 0:
        for link in SUSPECT_LINK:
            print bcolors.OKBLUE + "[+] " + bcolors.ENDC + link
    print(bcolors.OKGREEN + "--------------------" + bcolors.ENDC)
    print("# ADVANCED " + str(SUSPECT_AD) + " #")
    print(bcolors.OKGREEN + "--------------------" + bcolors.ENDC)
    for item in SUSPECT_ADVANCED:
        element = item.split(':')[0]
        last_modified = time.ctime(os.path.getmtime(element))
        date_creation = time.ctime(os.path.getctime(element))
        word = item.split(':')[1]
        print bcolors.WARNING + "[!] " + bcolors.ENDC + element
    print("\n")
    date_file = time.strftime("%H-%M-%S")
    with open(date_file+".txt",'a') as line:
        for item in SUSPECT_ADVANCED:
            site = item.split(':')[0]
            line.write(site+"\n")
    print bcolors.OKBLUE + "[!!] " + bcolors.ENDC + "python Audi.py --scan '"+ date_file + ".txt' "
    print bcolors.OKBLUE + "[!!] " + bcolors.ENDC + "python Audi.py --analyst '"+ date_file + ".txt' "

def Lfile(path):
    global SUSPECT
    global SUSPECT_LINK
    global SUSPECT_WORD
    with open(path,'r') as line:
        data = line.read()
        for element in SUSPECT_WORD:
            if element in data:
                if path not in SUSPECT_LINK:
                    result = advanced(path, element)
                    SUSPECT_LINK.append(path)
                    SUSPECT += 1

def audit(path):
    file_txt = glob.glob(path + "/*")
    for element in file_txt:
        if os.path.isfile(element):
            Lfile(element)
        else:
            audit(element)


def silenceAudit(link):
    try:
        date_creation = time.ctime(os.path.getctime(link))
        last_modified = time.ctime(os.path.getmtime(link))
        if date_creation != last_modified:
            print bcolors.WARNING + "[!] DATA MODIFIED " + bcolors.ENDC + link
    except:
        print bcolors.OKBLUE + "[!] WARNING ON  " + bcolors.ENDC + link

def main():
    if len(sys.argv) < 2:
        global SUSPECT
        print bcolors.OKGREEN + "[-] " + bcolors.ENDC + "SCAN STARTED"
        for i in tqdm(range(9)):
            listen_analyst('/Applications/MAMP/htdocs/backdoorred')
            #audit("/Applications/MAMP/htdocs/backdoorred")
            time.sleep(0.1)
        os.system('clear')
       #stats()
    elif sys.argv[1] == "--scan":
        for i in tqdm(range(100)):
            time.sleep(0.01)
        files = sys.argv[2]
        if files != "":
            while 1:
                with open(files,'r') as line:
                    data = line.readline()
                    new = data.replace("\n", "")
                    silenceAudit(new)
    elif sys.argv[1] == "--analyst":
        for i in tqdm(range(100)):
            time.sleep(0.01)
        files = sys.argv[2]
        if files != "":
            with open(files,'r') as line:
                data = line.readline()
                new = data.replace("\n", "")
                print bcolors.OKBLUE + "[ANALYST] "+new+"---------" + bcolors.ENDC
                os.system('cat '+new+" | grep shell_exec")
                os.system('cat '+new+" | grep eval")
            
#main()
while 1:
    listen_analyst("/Applications/MAMP/htdocs/backdoorred")