<?php

return [
    "csv"=>[
        "54"=>["id","openid","name","telephone","city","email"],
        "trade"=>["id","trade_time"],
        "stock"=>["code","name","date"],
        "influence"=>["date","code","name","closePrice","highPrice","lowPrice","openPrice","preClosePrice","incMoney","increase","transaction","tradeTotal","tradeMoney","totalMoney","influenceMoney","avgTradePrice"]
    ],
    "export"=>[
    	"scroll"=>"1m",
    	"size"=>500,
    	"type"=>"stock",
    	"filename"=>"Export_{{filename}}_".date("Y-m-d"),
    ]
];
