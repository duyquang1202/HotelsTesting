
Hotels Testing (https://gist.github.com/nifr/a453c5ea912c116a2630)
========================
url to scrape: $point = "http://vi.hotels.com/hotel/details.html?tab=description&q-localised-check-in=$checkInDate&hotel-id=555246&q-room-0-adults=2&YGF=1&MGT=7&WOE=7&q-localised-check-out=$checkOutDate&WOD=6&ZSX=0&SYE=3&q-room-0-children=0";
Setup:
1. setup environment (http://hotels.local)
2. Config Database:
   vi app/config/parameters.yml
3. run database: php app/console doctrine:schema:update --force
4. run: composer update
5. vi /etc/hosts
     127.0.0.1 hotels.local
6. unittest: phpunit -c app src/HotelsBundle/Tests/Controller/OfferControllerTest.php
7. functional test:  phpunit -c app src/HotelsBundle/Tests/Controller/OfferControllerFunctionalTest.php
8. Behat test:  bin/behat


Note: We only use a parameter "date" to scrape data