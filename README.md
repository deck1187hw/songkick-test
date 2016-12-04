![alt tag](http://miguelpuig.com/dev/songkick/songkick.png)


# Sonkick Test

## Synopsis

Write software that scrapes concert information from the pages on this site and outputs the data in a machine readable format of your choice (e.g. JSON, XML, CSV etc.).


## Requirments
- PHP 5.5>
- Apache Web Server
- PHP CURL extension (Optional, only for the accurate adddress)


## Installation

1. Unzip the whole file into your root apache directory in a new folder named songkick
2. Visit http://localhost/songkick/index.php/events/getAllEvents/?places=1
3. Main code is on the Events controller application/controllers/Events.php
4. The main method is getAllEvents() and accepts different parameters (see examples below)


## Examples

### 1. Return page x of Events in **Debug mode**

Parameters page=x where x is the page number. It will return the 10 first records

Example:

http://localhost/songkick/index.php/events/getAllEvents/?page=1

Output:

    [0] => stdClass Object
        (
            [artist] => 99 CLUB PICCADILLY CIRCUS  - SUN 4TH DECEMBER
            [venue] => The Queens Head, 15 Denman Street, London, W1D 7HN
            [date] => Sun 4th Dec, 2016 
            [final_price] => £20
        )

    ...



### 2. Return page x of Events in **Json output**

parameters **page=x** where x is the page number. It will return the 10 first records

Example:

http://localhost/songkick/index.php/events/getAllEvents/?page=1&format=json

Output:

    [{"artist":"99 CLUB PICCADILLY CIRCUS - SUN 4TH DECEMBER","venue":"The Queens Head, 15 Denman Street, London, W1D 7HN","date":"Sun 4th Dec, 2016 ","final_price":"£20"},{"artist":"AMY RIGBY","venue":"KINGSTON-UPON-HULL: Furley & Co","date":"Sun 4th Dec, 2016, 7:00pm","final_price":"\u00c2\u00a311.00"},(...)]
    


### 3. Return events with accurate **location and address**

Parameters **places=1** where x is the page number.

Example:

http://miguelpuig.com/dev/songkick/index.php/events/getAllEvents/?page=5&places=1&format=json

Output:

    [0] => stdClass Object
            (
                [artist] => CHARLEY'S AUNT
                [venue] => LONDON: The London Theatre
                [address] => stdClass Object
                    (
                        [name] => Array
                            (
                                [0] => National Theatre
                                [1] => Royal National Theatre
                            )

                        [countryName] => United Kingdom
                        [countryCode] => gb
                        [coords] => stdClass Object
                            (
                                [lat] => 51.507
                                [lng] => -0.1141
                            )

                    )

                [date] => Sun 4th Dec, 2016, 5:00pm
                [final_price] => Â£13.20
            )


### 4. Return **all events** available

Note, this will take a while, make sure you have configured on your php.ini the max_execution_time accordingly

Example:

http://localhost/songkick/index.php/events/getAllEvents/all




## License

MIT