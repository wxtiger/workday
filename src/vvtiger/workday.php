<?php

namespace vvtiger;

class Workday{

    public $verType = 'DEV';
	public $version = '1.0';
	
    public $configHolidayFile='';
    public $configHolidayYear='';
    public $configHolidayArea='';
    public $arrConfigHoliday;
    public $arrConfigWork;

    public function __construct($areaName='chinese'){

        $areaName = strtolower($areaName);
        if($areaName='chinese'||$areaName=''){
            $this->configHolidayFile = __DIR__.'/json/chinese.json';     
        }else{
            $this->configHolidayFile = __DIR__.'/json/chinese.json';  
        }      
        
        //默认当前年
        $this->getConfigHoliday('');

    }


    //获得两个时间之间的办公日期,
    public function getDay($sDateFrom, $sDateTo,$sType='All'){

        $arrReturn = array();

        $dateFrom = strtotime($sDateFrom);
        $dateTo = strtotime($sDateTo);
        if(!$dateFrom || !$dateTo) return $arrReturn;
        if($dateTo<$dateFrom) return $arrReturn;

        //$arrConfigWork      = array();    //系统配置的，法定周末上班日期
        //$arrConfigHoliday   = array();    //系统配置的，周一到周五的法定放假日期

        $arrAll         = array();  //所有日期
        $arrWeekEnd     = array();  //收集周末两天
        $arrWeekDay     = array();  //收集非周末两天，就是每周五天
        $arrWork        = array();  //收集的法定工作日
        $arrHoliday     = array();  //收集的法定假日
        $arrStockWork   = array();  //股市交易时间，既要排除掉法定休息日，也要排除掉所有周末两天

        $dateCur = $dateFrom;
        $i = 0;
        while(true){

            //获得配置假期
            $this->getConfigHoliday(date("Y",$dateCur));

            $sDate = date("Y-m-d",$dateCur);
            $numW = (int)date("w",$dateCur);

            //开始判断和检查工作属性
            $arrAll[] = $sDate;
            if($numW==0 || $numW==6){
                $arrWeekEnd[] = $sDate;  //收集周末两天

                if(in_array($sDate,$this->arrConfigWork)){
                    //周末，要上班
                    $arrWork[] = $sDate;
                }else{
                    //周末，正常周末放假
                    $arrHoliday[] = $sDate;
                }
            }else{
                //非周末，
                $arrWeekDay[] = $sDate;
                
                if(in_array($sDate,$this->arrConfigHoliday)){
                    //非周末，法定休假日
                    $arrHoliday[] = $sDate;
                }else{
                    //非周末，正常工作日
                    $arrWork[] = $sDate;
                    $arrStockWork[] = $sDate;
                }
            }

            //下一天
            $dateCur = strtotime('+1 days',$dateCur);
            if($dateCur>$dateTo) break;  
            $i++;
            if($i>=366) break;   //限制不能超过一年
        }

        if($sType=='All')       return $arrAll;
        if($sType=='Work')      return $arrWork;
        if($sType=='WeekEnd')   return $arrWeekEnd;
        if($sType=='WeekDay')   return $arrWeekDay;
        if($sType=='Holiday')   return $arrHoliday;
        if($sType=='StockWork') return $arrStockWork;

        return $arrAll;
    }    

    //检查指定的日期是否是工作日，
    public function checkWorkday($sdate=0){
        
        if(gettype($sdate)=='string'){
            $sdate = strtotime($sdate);
        }

        if(!$sdate && $sdate!=0 ) return 'No';
        
        if(is_string($sdate)){
            $dateFrom = strtotime($sdate);
        }else{
            $dateFrom = $sdate;
            if($sdate==0) $dateFrom = time();            
        } 
        $this->getConfigHoliday(date("Y",$dateFrom));    //获得假期配置


            $sDate = date("Y-m-d",$dateFrom);
            $numW = (int)date("w",$dateFrom);

            //开始判断和检查工作属性
            if($numW==0 || $numW==6){
                //周末两天
                if(in_array($sDate,$this->arrConfigWork)){
                    //周末，要上班
                    return 'Yes';
                }else{
                    //周末，正常周末放假
                    return 'No';
                }
            }else{
                //非周末，                
                if(in_array($sDate,$this->arrConfigHoliday)){
                    //非周末，法定休假日
                    return 'No';
                }else{
                    //非周末，正常工作日
                    return 'Yes';
                }
            }
        return 'No';
       
    }   

    //检查指定的日期的属性，
    public function checkDayType($sdate=0,$sType='All'){

        if(gettype($sdate)=='string'){
            $sdate = strtotime($sdate);
        }

        if(!$sdate && $sdate!=0 ) return 'No';
        
        if(is_string($sdate)){
            $dateFrom = strtotime($sdate);
        }else{
            $dateFrom = $sdate;
            if($sdate==0) $dateFrom = time();            
        } 
        if($sType=='All'||$sType=='') return 'Yes';

        //Weekend周末运行，WeekDay周末两天不运行，Work仅限法定工作日运行，StockWork股票工作日，Holiday法定假日包括周末
        $this->getConfigHoliday(date("Y",$dateFrom));    //获得假期配置

            $sDate = date("Y-m-d",$dateFrom);
            $numW = (int)date("w",$dateFrom);

            //开始判断和检查工作属性
            if($numW==0 || $numW==6){
                //周末两天
                if($sType=='Weekend') return 'Yes';
                if($sType=='WeekDay') return 'No';
                if($sType=='StockWork') return 'No';
                

                if(in_array($sDate,$this->arrConfigWork)){
                    //周末，要上班
                    if($sType=='Work') return 'Yes';
                    if($sType=='Holiday') return 'No';
                }else{
                    //周末，正常周末放假
                    if($sType=='Work') return 'No';
                    if($sType=='Holiday') return 'Yes';
                }
            }else{
                //非周末，     
                if($sType=='Weekend') return 'No';    
                if($sType=='WeekDay') return 'Yes';

                if(in_array($sDate,$this->arrConfigHoliday)){
                    //非周末，法定休假日
                    if($sType=='Work') return 'No';                    
                    if($sType=='StockWork') return 'No';
                    if($sType=='Holiday') return 'Yes';
                }else{
                    //非周末，正常工作日
                    if($sType=='Work') return 'Yes';                    
                    if($sType=='StockWork') return 'Yes';
                    if($sType=='Holiday') return 'No';
                }
            }


        return 'No';
        
    }   

    //获得系统配置假期
    public function getConfigHoliday($sYear=''){
        if($sYear=='') $sYear = date("Y");

        $this->arrConfigHoliday = array();
        $this->arrConfigWork = array();

        //如果相同，就不搜索了，
        if($this->configHolidayYear == $sYear) return;
        if($this->configHolidayFile == '') return;
        $this->configHolidayYear = $sYear;

        $sTemp = file_get_contents($this->configHolidayFile);
        if($sTemp=='') return;

        $numYear = (int)$sYear;//目前仅配置了2021,2022；

        $obj = json_decode($sTemp,true);
        if(!$obj) return;

        $arrYear = $obj['YearList'];
        if(!$arrYear) return;

        foreach($arrYear as $objYear){
            if($objYear['Year'] == $sYear){
                $sTemp = $objYear['Holiday'];
                if($sTemp!=''){
                    $arrTemp = explode(',',$sTemp);
                    foreach($arrTemp as $sDay){
                        $this->arrConfigHoliday[] = $sYear.'-'.$sDay;
                    }
                }

                $sTemp = $objYear['WeekWork'];
                if($sTemp!=''){
                    $arrTemp = explode(',',$sTemp);
                    foreach($arrTemp as $sDay){
                        $this->arrConfigWork[] = $sYear.'-'.$sDay;
                    }
                }
                return;
            }
        }
        return;
    }

    //获得指定工作日以后几天的日期
    public function addDay($sFromDate,$dayNum,$sType='All'){
        
        if(is_string($sFromDate)){
            if($sFromDate=='') $dateFrom = time();
            else $dateFrom = strtotime($sFromDate);
        }else{
            $dateFrom = $sFromDate;
            if($sdate==0) $dateFrom = time();            
        } 
        //if($sType=='All'||$sType=='') return 'Yes';
        
        if($dayNum==0) return $dateFrom;
        $addType = '+';
        if($dayNum<0) $addType = '-';
        
        $num = 0;
        $numOK = 0;
        while(true){
            
            $num++;

            $sTemp = $addType.((string)$num).' days';
            $dateCur = strtotime($sTemp,$dateFrom);
            
            $result = $this->checkDayType($dateCur,$sType);
            if($result == 'Yes'){
                $numOK++;
                if($numOK>=abs($dayNum)) return $dateCur;
            }

            if($num>=366) return $dateCur;
        }
        
    }

}
