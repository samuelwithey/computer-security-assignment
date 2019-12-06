#!/bin/bash
# commands used to complete task 3

crunch 3 3 -f /usr/share/rainbowcrack/charset.txt mixalpha -o password-dict-mixalpha.txt
git clone git://github.com/magnumripper/JohnTheRipper
cd JohnTheRipper/src/
./configure && make -s clean && make -sj4
cd ../run/
./pdf2john.pl /root/Documents/'Group - SurnameStartsFromLtoZ'/'PasswordProtectedFile[L to Z].pdf' > /root/Documents/pdf.txt
cd ../../
john --wordlist=/root/Documents/password-dict-mixalpha.txt /root/Documents/pdf.txt
john --show --format=PDF pdf.txt



