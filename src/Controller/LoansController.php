<?php

/* Controller for setting up Loans Program */

class LoansController extends AppController {

    var $name = 'Loans';
    var $uses = array('Loans');
    public $actsAs = array('Containable');

    public function beforeFilter() {
        ini_set('max_execution_time', 300); //300 seconds = 5 minutes
        parent::beforeFilter();
        $this->Auth->allow(array('company_twlmonth', 'company_onemonth', 'company_tenyear', 'company_ten'));
    }

    public function company_index() {
        $this->layout = 'ajax';
        $this->loadModel('Loanratesheet');
        $loanratesheet = $this->Loanratesheet->find('first', array(
            'order' => 'sheet_date DESC'
                )
        );
        $this->set('loanratesheet', $loanratesheet);

        $results = json_decode(file_get_contents("ratecharts.json"));
        $this->set("results", $results);
    }

    public function company_getlenders() {
        $this->layout = 'ajax';
        $this->loadModel('Lender');
        $this->loadModel('LenderRateSheet');

        $this->Lender->bindModel(
                array(
                    'hasMany' => array(
                        'LenderRateSheet' => array(
                            'className' => 'LenderRateSheet',
                            'foreignKey' => 'lender_id',
                            'limit' => 1,
                            'order' => 'id DESC',
                        )
                    )
                )
        );
        $lenders = $this->Lender->find('all', array(
            'conditions' => array(
                'Lender.user_id' => $_SESSION['Auth']['User']['id'],
                'Lender.status' => 1,
        )));
        $this->set('lenders', $lenders);
    }

    public function company_ratesheet() {
        $this->layout = 'ajax';
        $this->loadModel('LenderSheet');
        if (isset($this->request->data) && !empty($this->request->data)) {
            if (isset($this->request->data['LenderSheet']['file']) && !empty($this->request->data['LenderSheet']['file'])) {
                $name = $this->request->data['LenderSheet']['file']['name'];
                $fname = time() . "_" . $name;
                move_uploaded_file($this->request->data['LenderSheet']['file']['tmp_name'], "upload/Lenders/Ratesheet/" . $fname);
                $this->request->data['LenderSheet']['file'] = $fname;
                $this->LenderSheet->save($this->request->data);
                $response = array('status' => true, 'time' => date('m/d/Y - H:ia'), time());
                echo json_encode($response);
                exit;
            } else {
                $response = array('status' => false, 'data' => false);
                echo json_encode($response);
                exit;
            }
        }
    }

    public function company_sheets($lender_id) {
        $this->layout = 'ajax';
        $this->loadModel('LenderSheet');
        $loansheets = $this->LenderSheet->find('all', array('conditions' => array(
                'lender_id' => $lender_id
        )));
        $this->set('loansheets', $loansheets);
    }

    public function company_pricing($id = null) {
        $this->layout = 'ajax';
        if ($id == null) {
            die("You are not authorized to access this page!");
        }

        $this->loadModel('Lender');
        $lender = $this->Lender->findById($id);
        $this->set('lender', $lender);

//        $this->loadModel('Loanprogram');
//        $this->Loanprogram->hasMany['Programmargin']['conditions'] = array('margin !=' => '');
//        $loanprograms = $this->Loanprogram->find('all', array(
//            'conditions' => array(
//                'lender_id' => $id
//            )
//        ));
//        $this->set('loanprograms', $loanprograms);

        $this->loadModel('LenderSheet');
        $lendersheet = $this->LenderSheet->find('first', array(
            'conditions' => array(
                'lender_id' => $id
            ),
            'order' => 'id DESC'
        ));
        $this->set('lendersheet', $lendersheet);


        $this->loadModel('LenderRateSheet');
        $lenderpricesheet = $this->LenderRateSheet->find('first', array(
            'conditions' => array(
                'lender_id' => $id
            ),
            'order' => 'id DESC'
        ));
        $this->set('lenderpricesheet', $lenderpricesheet);
    }

    public function company_newpricing($id = null) {
        $this->layout = 'ajax';
        if ($id == null) {
            die("You are not authorized to access this page!");
        }

        $this->loadModel('Lender');
        $lender = $this->Lender->findById($id);
        $this->set('lender', $lender);

        $this->loadModel('LenderSheet');
        $lendersheet = $this->LenderSheet->find('first', array(
            'conditions' => array(
                'lender_id' => $id
            ),
            'order' => 'id DESC'
        ));
        $this->set('lendersheet', $lendersheet);


        $this->loadModel('LenderRateSheet');
        $lenderpricesheet = $this->LenderRateSheet->find('first', array(
            'conditions' => array(
                'lender_id' => $id
            ),
            'order' => 'id DESC'
        ));
        $this->set('lenderpricesheet', $lenderpricesheet);
    }

    public function company_allloanprogram($id = null) {
        $this->layout = 'ajax';
        if ($id == null) {
            die("You are not authorized to access this page!");
        }

        $this->loadModel('Lender');
        $lender = $this->Lender->findById($id);
        $this->set('lender', $lender);


        $this->loadModel('HudInput');
        $hubinputs = $this->HudInput->findById($id);
        $this->set('hubinputs', $hubinputs);

        $this->loadModel('Loanprogram');
        $this->Loanprogram->recursive = 2;
        $this->Loanprogram->hasMany['Programmargin']['conditions'] = array('margin !=' => '');
        $loanprograms = $this->Loanprogram->find('all', array(
            'conditions' => array(
                'lender_id' => $id
            )
        ));
        $this->set('loanprograms', $loanprograms);

        $this->loadModel('LenderRateSheet');
        $lenderpricesheet = $this->LenderRateSheet->find('first', array(
            'conditions' => array(
                'lender_id' => $id
            ),
            'order' => 'id DESC'
        ));
        $this->set('lenderpricesheet', $lenderpricesheet);
    }

    public function company_allloanprogramnew($id = null) {
        $this->layout = 'ajax';
        if ($id == null) {
            die("You are not authorized to access this page!");
        }

        $this->loadModel('Lender');
        $lender = $this->Lender->findById($id);
        $this->set('lender', $lender);


        $this->loadModel('HudInput');
        $hubinputs = $this->HudInput->findById($id);
        $this->set('hubinputs', $hubinputs);

        $this->loadModel('Loanprogram');
        $this->Loanprogram->recursive = 2;
        $this->Loanprogram->hasMany['Programmargin']['conditions'] = array('margin !=' => '');
        $loanprograms = $this->Loanprogram->find('all', array(
            'conditions' => array(
                'lender_id' => $id
            )
        ));
        $this->set('loanprograms', $loanprograms);

        $this->loadModel('LenderRateSheet');
        $lenderpricesheet = $this->LenderRateSheet->find('first', array(
            'conditions' => array(
                'lender_id' => $id
            ),
            'order' => 'id DESC'
        ));
        $this->set('lenderpricesheet', $lenderpricesheet);
    }

    public function company_refreshprogram($id = null) {
        $this->layout = 'ajax';
        if ($id == null) {
            die("You are not authorized to access this page!");
        }
        $this->loadModel('Loanprogram');
        $this->Loanprogram->hasMany['Programmargin']['conditions'] = array('margin !=' => '');
        $loanprograms = $this->Loanprogram->find('first', array(
            'conditions' => array(
                'id' => $id
            )
        ));
        $this->set('loanprograms', $loanprograms);

        $this->loadModel('LenderRateSheet');
        $lenderpricesheet = $this->LenderRateSheet->find('first', array(
            'conditions' => array(
                'lender_id' => $id
            ),
            'order' => 'id DESC'
        ));
        $this->set('lenderpricesheet', $lenderpricesheet);
    }

    public function company_trends($lender_id = null) {
        $this->layout = 'ajax';
        if ($lender_id == null) {
            die("You are not authorized to access this page!");
        }

        $this->loadModel('LenderRateSheet');
        $lenderpricesheet = $this->LenderRateSheet->find('all', array(
            'conditions' => array(
                'lender_id' => $lender_id
            ),
            'order' => 'id DESC',
            'limit' => 30
        ));
        $this->set('lenderpricesheet', $lenderpricesheet);

        $this->loadModel('Lender');
        $lender = $this->Lender->findById($lender_id);
        $this->set('lender', $lender);
    }

    public function company_programs($id = null) {
        $this->layout = 'ajax';
        if ($id == null) {
            die("You are not authorized to access this page!");
        }
        $this->loadModel('Loanprogram');
        $loanprogram = $this->Loanprogram->findById($id);
        $this->set('loanprogram', $loanprogram);

        if ($loanprogram['Loanprogram']['types'] == 'fixed') {
            $this->render('company_fprogram');
        }
    }

    public function company_update() {
        $this->layout = 'ajax';
//pr($this->request->data); exit;
        if ($this->request->data) {
            $this->loadModel('Loanprogram');
            $pdata = array('Loanprogram' => $this->request->data['Loanprogram']);
            $this->Loanprogram->save($pdata);

            if (isset($this->request->data['Loanpricing'])) {
                $this->loadModel('Loanpricing');
                $this->Loanpricing->saveAll($this->request->data['Loanpricing']);
            }

            if (isset($this->request->data['Programmargin'])) {
                $this->loadModel('Programmargin');
                $this->Programmargin->saveAll($this->request->data['Programmargin']);
            }

            if (isset($this->request->data['Programfixedrate'])) {
                $this->loadModel('Programfixedrate');
                $this->Programfixedrate->saveAll($this->request->data['Programfixedrate']);
            }

            echo json_encode(array('status' => true, 'lender_id' => $this->request->data['Loanprogram']['lender_id']));
            exit;
        } else {
            echo json_encode(array('status' => false));
            exit;
        }
    }

    public function company_changeprice() {
        $this->layout = 'ajax';
        $this->loadModel('Loanpricingvalue');
        if ($this->request->data) {
            unset($this->request->data['rand']);
            $check = $this->Loanpricingvalue->find('first', array(
                'conditions' => array(
                    'lender_id' => $this->request->data['lender_id'],
                    'program_id' => $this->request->data['program_id'],
                    'm_id' => $this->request->data['m_id'],
                    'p_id' => $this->request->data['p_id'],
                    'set_date' => date('Y-m-d'),
                )
            ));
            if ($check) {
                $this->request->data['id'] = $check['Loanpricingvalue']['id'];
            }
            $this->request->data['set_date'] = date('Y-m-d');
//            pr($this->request->data); exit;
            $data = array('Loanpricingvalue' => $this->request->data);
            $this->Loanpricingvalue->save($data);
            echo json_encode(array('status' => true));
            exit;
        } else {
            echo json_encode(array('status' => false));
            exit;
        }
    }

    public function company_changefprice() {
        $this->layout = 'ajax';
        $this->loadModel('Programfixedrate');
        if ($this->request->data) {
            unset($this->request->data['rand']);
            $pdata['id'] = $this->request->data['id'];
            $pdata[$this->request->data['field']] = $this->request->data['set_value'];
            $data = array('Programfixedrate' => $pdata);
//            pr($data); 
            $this->Programfixedrate->save($data);
            echo json_encode(array('status' => true));
            exit;
        } else {
            echo json_encode(array('status' => false));
            exit;
        }
    }

    public function company_changece() {
        $this->layout = 'ajax';
        $this->loadModel('Programmargin');
        if ($this->request->data) {
            $this->request->data['set_date'] = date('Y-m-d');
            $data = array('id' => $this->request->data['id'], $this->request->data['field'] => $this->request->data['value']);
            $this->Programmargin->save($data);
            echo json_encode(array('status' => true));
            exit;
        } else {
            echo json_encode(array('status' => false));
            exit;
        }
    }

    public function company_changfece() {
        $this->layout = 'ajax';
        $this->loadModel('Programfixedrate');
        if ($this->request->data) {
            $this->request->data['set_date'] = date('Y-m-d');
            $data = array('id' => $this->request->data['id'], $this->request->data['field'] => $this->request->data['value']);
//            pr($data);exit;
            $this->Programfixedrate->save($data);
            echo json_encode(array('status' => true));
            exit;
        } else {
            echo json_encode(array('status' => false));
            exit;
        }
    }

    public function company_changeas() {
        $this->layout = 'ajax';
        $this->loadModel('Loanprogram');
        if ($this->request->data) {
            $this->request->data['set_date'] = date('Y-m-d');
            $data = array('id' => $this->request->data['id'], 'activate' => $this->request->data['value']);
//            pr($data);exit;
            $this->Loanprogram->save($data);
            echo json_encode(array('status' => true));
            exit;
        } else {
            echo json_encode(array('status' => false));
            exit;
        }
    }

    public function company_deletesheet($id = null) {
        $this->layout = 'ajax';
        $this->loadModel('LenderSheet');
        if ($id != null) {
            $this->LenderSheet->delete($id);
            $response = array('status' => true);
        } else {
            $response = array('status' => false);
        }
        echo json_encode($response);
        exit;
    }

    public function company_opendocument($id) {
        $this->layout = 'ajax';
        $this->loadModel('LenderSheet');
        $data = $this->LenderSheet->findById($id);
        if ($data) {
            $fullpath = 'upload/Lenders/Ratesheet/' . $data['LenderSheet']['file'];
            $type = $this->get_mime_content_type($fullpath);
            $size = $document['LenderSheet']['size'];
            if (!file_exists($fullpath)) {
                die("File not found");
            }
            header('Pragma: public');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Cache-Control: private', false);
            header('Content-Type: ' . $type);
            header('Content-Disposition: attachment; filename="' . basename($fullpath) . '"');
            header('Content-Transfer-Encoding: binary');
            header('Content-Length: ' . $size);
            header('Connection: close');
            readfile($fullpath);
            exit;
        } else {
            die('Ooops...');
        }
    }

    public function httpGet($url) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//  curl_setopt($ch,CURLOPT_HEADER, false); 

        $output = curl_exec($ch);

        curl_close($ch);
        return $output;
    }

    public function company_test() {

        $date = date('Y-m-d', strtotime('-1 year'));
        $data = $this->httpGet("https://www.quandl.com/api/v3/datasets/FRED/DSWP10.json?api_key=xGv2Mk5o8rauTs4TN8z_&order=asc&collapse=monthly&limit=12");

        $data1 = $this->httpGet("https://www.quandl.com/api/v3/datasets/FRED/USD1MTD156N.json?api_key=xGv2Mk5o8rauTs4TN8z_&order=asc&collapse=monthly&limit=12");

        $data2 = $this->httpGet("https://www.quandl.com/api/v3/datasets/FRED/USD12MD156N.json?api_key=xGv2Mk5o8rauTs4TN8z_&order=asc&collapse=monthly&limit=12");

        $dataList = json_decode($data);
        $dataList1 = json_decode($data1);
        $dataList2 = json_decode($data2);
        $months = array();
        $records = array();
        $records1 = array();
        $records2 = array();
        foreach ($dataList->dataset->data as $key => $val) {
            $month = date('M', strtotime($val[0]));
            $months[] = $month;
            $records[] = $val[1];
            $records1[] = $dataList1->dataset->data[$key][1];
            $records2[] = $dataList2->dataset->data[$key][1];
        }
        pr($records);
        pr($records1);
        pr($records2);
        pr($months);
        $this->set('months', implode(',', $months));
        $this->set('record', implode(',', $records));
        $this->set('record1', implode(',', $records1));
        $this->set('record2', implode(',', $records2));
        die();
        $this->loadModel('Loanratesheet');

//        $loan = $this->Loanratesheet->find('first', array('order' => 'id DESC', 'limit' => 1));
        $qry = "";
//        if ($loan) {
//            $sheet_date = $loan['Loanratesheet']['sheet_date'];
//            $sheet_date1 = date('Y-m-d', strtotime($sheet_date) + 86400);
//            $qry = "&start_date=$sheet_date1";
//        }
        // 10 Year Swap Rate API
//        $data = $this->httpGet("https://www.quandl.com/api/v3/datasets/FRED/DSWP10.json?api_key=xGv2Mk5o8rauTs4TN8z_&order=asc&transform=diff" . $qry);
        // 1 Month Libor Rate API
//        $data = $this->httpGet("https://www.quandl.com/api/v3/datasets/FRED/USD1MTD156N.json?api_key=xGv2Mk5o8rauTs4TN8z_&order=asc&start_date=2000-07-03&transform=diff");


        $data = $this->httpGet("https://www.quandl.com/api/v3/datasets/FRED/USD12MD156N.json?api_key=xGv2Mk5o8rauTs4TN8z_&start_date=2000-07-03&order=asc&transform=diff");
        //&transform=diff

        $dataList = json_decode($data);
        $datasave = array();
        foreach ($dataList->dataset->data as $value) {
            $date = $value[0];
            $loan = $this->Loanratesheet->find('first', array('conditions' => array('sheet_date' => $date)));
            if ($loan) {
                $id = $loan['Loanratesheet']['id'];
                $c1 = $value[1];
                $datasave[] = array(
                    'id' => $id,
                    '1year_diff' => $c1
                );
            }
        }
        echo count($datasave);
        $this->Loanratesheet->saveAll($datasave);
        exit;
    }

    public function company_onemonth() {
        $this->layout = 'ajax';
        $this->loadModel('Loanratesheet');
        require 'simple_html_dom.php';
        $html = file_get_html('http://www.global-rates.com/interest-rates/libor/american-dollar/usd-libor-interest-rate-1-month.aspx');
        $fdate = $html->find('tr.tabledata1 td', 0)->plaintext;
        $finalDate = date('Y-m-d', strtotime(str_replace('&nbsp;', '', $fdate)));
        $latestData = str_replace("&nbsp;%", '', $html->find('tr.tabledata1 td', 1)->plaintext);
        $isExist = $this->Loanratesheet->find('first', array(
            'conditions' => array(
                'sheet_date' => $finalDate
            )
        ));
        $list = array(
            'sheet_date' => $finalDate,
            '1month_libor' => $latestData
        );
        if ($isExist) {
            $list['id'] = $isExist['Loanratesheet']['id'];
            $lists = $this->Loanratesheet->find('first', array(
                'conditions' => array(
                    'sheet_date <' => $finalDate
                ),
                'order' => 'sheet_date DESC'
            ));
            $list['1month_diff'] = $latestData - $lists['Loanratesheet']['1month_libor'];
        } else {
            $lists = $this->Loanratesheet->find('first', array(
                'order' => 'sheet_date DESC'
            ));
            $list['1month_diff'] = $latestData - $lists['Loanratesheet']['1month_libor'];
        }
        $this->Loanratesheet->save($list);
        pr($list);
        exit;
        die("hey");
    }

    public function company_twlmonth() {
        $this->layout = 'ajax';
        $this->loadModel('Loanratesheet');
        require 'simple_html_dom.php';
        $html = file_get_html('http://www.global-rates.com/interest-rates/libor/american-dollar/usd-libor-interest-rate-12-months.aspx');
        $fdate = $html->find('tr.tabledata1 td', 0)->plaintext;
        $finalDate = date('Y-m-d', strtotime(str_replace('&nbsp;', '', $fdate)));
        $latestData = str_replace("&nbsp;%", '', $html->find('tr.tabledata1 td', 1)->plaintext);
        $isExist = $this->Loanratesheet->find('first', array(
            'conditions' => array(
                'sheet_date' => $finalDate
            )
        ));
        $list = array(
            'sheet_date' => $finalDate,
            '1year_libor' => $latestData
        );
        if ($isExist) {
            $list['id'] = $isExist['Loanratesheet']['id'];
            $lists = $this->Loanratesheet->find('first', array(
                'conditions' => array(
                    'sheet_date <' => $finalDate
                ),
                'order' => 'sheet_date DESC'
            ));
            $list['1year_diff'] = $latestData - $lists['Loanratesheet']['1year_libor'];
        } else {
            $lists = $this->Loanratesheet->find('first', array(
                'order' => 'sheet_date DESC'
            ));
            $list['1year_diff'] = $latestData - $lists['Loanratesheet']['1year_libor'];
        }
        $this->Loanratesheet->save($list);
        pr($list);
        exit;
        die("hey");
    }

    public function company_ten() {
        $this->layout = 'ajax';
        $this->loadModel('Loanratesheet');
        require 'simple_html_dom.php';
        $html = file_get_html('http://www.barchart.com/quotes/rates/SWAADY10.RT');
        $latestData = trim(str_replace("%", '', $html->find('#divQuotePageHeader #dtaLast', 0)->plaintext));
        $fdate = rtrim(ltrim($html->find('.qb_line .smgrey #dtaDate', 0)->plaintext));
        $finalDate = date('Y-m-d', strtotime(str_replace('&nbsp;', '', $fdate)));


        $isExist = $this->Loanratesheet->find('first', array(
            'conditions' => array(
                'sheet_date' => $finalDate
            )
        ));
        $list = array(
            'sheet_date' => $finalDate,
            '10year_swap' => $latestData
        );
        if ($isExist) {
            $list['id'] = $isExist['Loanratesheet']['id'];
            $lists = $this->Loanratesheet->find('first', array(
                'conditions' => array(
                    'sheet_date <' => $finalDate
                ),
                'order' => 'sheet_date DESC'
            ));
            $list['10year_diff'] = $latestData - $lists['Loanratesheet']['10year_swap'];
        } else {
            $lists = $this->Loanratesheet->find('first', array(
                'order' => 'sheet_date DESC'
            ));
            $list['10year_diff'] = $latestData - $lists['Loanratesheet']['10year_swap'];
        }
        $this->Loanratesheet->save($list);
        pr($list);
        exit;
        die("hey");
    }

    public function company_tenyear() {
        $this->layout = 'ajax';
        $this->loadModel('Loanratesheet');

        $data = $this->httpGet("https://www.quandl.com/api/v3/datasets/FRED/DSWP10.json?api_key=xGv2Mk5o8rauTs4TN8z_&limit=1");
        $dataList = json_decode($data);
        $fdate = $dataList->dataset->data[0][0];
        $latestData = $dataList->dataset->data[0][1];
        $isExist = $this->Loanratesheet->find('first', array(
            'conditions' => array(
                'sheet_date' => $fdate
            )
        ));
        $list = array(
            'sheet_date' => $fdate,
            '10year_swap' => $latestData
        );
        if ($isExist) {
            $list['id'] = $isExist['Loanratesheet']['id'];
            $lists = $this->Loanratesheet->find('first', array(
                'conditions' => array(
                    'sheet_date <' => $fdate
                ),
                'order' => 'sheet_date DESC'
            ));
            $list['10year_diff'] = $latestData - $lists['Loanratesheet']['10year_swap'];
        } else {
            $lists = $this->Loanratesheet->find('first', array(
                'order' => 'sheet_date DESC'
            ));
            $list['10year_diff'] = $latestData - $lists['Loanratesheet']['10year_swap'];
        }
        $this->Loanratesheet->save($list);
        pr($list);
        exit;
        die("hey");
    }

    public function company_updatepricing($id) {
        $this->layout = 'ajax';
        $this->loadModel('LenderRateSheet');
        $current = $this->request->data['LenderRateSheet'];
//        pr($current); exit;
        $date = date('Y-m-d');
        $datapost = array();

        $datapost['LenderRateSheet']['sheet_date'] = $date;
        $datapost['LenderRateSheet']['lender_id'] = $id;
        $datapost['LenderRateSheet']['10year_swap'] = $current['tenyearswap'];
        $datapost['LenderRateSheet']['1month_libor'] = $current['onemonthlibor'];
        $datapost['LenderRateSheet']['1year_libor'] = $current['oneyearlibor'];
//        $datapost['LenderRateSheet']['max_pl_margin'] = $current['maxplmargin'];


        $data = $this->LenderRateSheet->find('first', array(
            'conditions' => array(
                'sheet_date' => $date,
                'lender_id' => $id,
            ),
            'order' => 'id DESC'
        ));
        if ($data) {
            $datapost['LenderRateSheet']['id'] = $data['LenderRateSheet']['id'];
        }

        $prevdata = $this->LenderRateSheet->find('first', array(
            'conditions' => array(
                'sheet_date !=' => $date,
                'lender_id' => $id,
            ),
            'order' => 'id DESC'
        ));

        $listArray = array(
            'ten_year' => 0,
            'one_month' => 0,
            'tw_month' => 0,
            'pl_margin' => 0,
        );

        if ($prevdata) {
            $length = strlen(substr(strrchr($current['onemonthlibor'], "."), 1));
            $length_n = strlen(substr(strrchr($prevdata['LenderRateSheet']['1month_libor'], "."), 1));
            if ($length < $length_n) {
                $length = $length_n;
            }
            $datapost['LenderRateSheet']['1month_diff'] = number_format($current['onemonthlibor'] - $prevdata['LenderRateSheet']['1month_libor'], $length);


            $length1 = strlen(substr(strrchr($current['tenyearswap'], "."), 1));
            $length_n1 = strlen(substr(strrchr($prevdata['LenderRateSheet']['10year_swap'], "."), 1));
            if ($length1 < $length_n1) {
                $length1 = $length_n1;
            }
            $datapost['LenderRateSheet']['10year_diff'] = number_format($current['tenyearswap'] - $prevdata['LenderRateSheet']['10year_swap'], $length1);



            $length2 = strlen(substr(strrchr($current['oneyearlibor'], "."), 1));
            $length_n2 = strlen(substr(strrchr($prevdata['LenderRateSheet']['1month_libor'], "."), 1));
            if ($length2 < $length_n2) {
                $length2 = $length_n2;
            }
            $datapost['LenderRateSheet']['1year_diff'] = number_format($current['oneyearlibor'] - $prevdata['LenderRateSheet']['1year_libor'], $length);

            $datapost['LenderRateSheet']['max_pl_margin'] = $current['maxplmargin'];
            $datapost['LenderRateSheet']['max_pl_margin_diff'] = $current['maxplmargin'] - $prevdata['LenderRateSheet']['max_pl_margin'];

            $ten_year = true;
            $one_month = true;
            $pl_margin = true;
            $tw_month = true;
            if ($datapost['LenderRateSheet']['10year_diff'] >= 0) {
                $ten_year = false;
            }
            if ($datapost['LenderRateSheet']['1month_diff'] >= 0) {
                $one_month = false;
            }
            if ($datapost['LenderRateSheet']['1year_diff'] >= 0) {
                $tw_month = false;
            }
            if ($datapost['LenderRateSheet']['max_pl_margin_diff'] >= 0) {
                $pl_margin = false;
            }
            $listArray = array(
                'ten_year' => $ten_year,
                'one_month' => $one_month,
                'tw_month' => $tw_month,
                'pl_margin' => $pl_margin,
            );
        }
        $this->LenderRateSheet->save($datapost);





        $response = array('status' => true, 'data' => $datapost, 'diff' => $listArray);
        echo json_encode($response);
        exit;
        pr($datapost);
        exit;
    }

    public function company_generate() {
        $this->layout = 'ajax';
        $data = $this->httpGet("https://www.quandl.com/api/v3/datasets/FRED/DSWP10.json?api_key=xGv2Mk5o8rauTs4TN8z_&order=desc&collapse=monthly&limit=12");

        $data1 = $this->httpGet("https://www.quandl.com/api/v3/datasets/FRED/USD1MTD156N.json?api_key=xGv2Mk5o8rauTs4TN8z_&order=desc&collapse=monthly&limit=12");

        $data2 = $this->httpGet("https://www.quandl.com/api/v3/datasets/FRED/USD12MD156N.json?api_key=xGv2Mk5o8rauTs4TN8z_&order=desc&collapse=monthly&limit=12");

        $dataList = json_decode($data);
        $dataList1 = json_decode($data1);
        $dataList2 = json_decode($data2);

        foreach ($dataList->dataset->data as $key => $val) {
            $month = date('M, Y', strtotime($val[0]));
            $months[] = $month;
            $records[] = $val[1];
            $records1[] = $dataList1->dataset->data[$key][1];
            $records2[] = $dataList2->dataset->data[$key][1];
        }

        $ar = array();
        $months = array_reverse($months);
        $records = array_reverse($records);
        $records1 = array_reverse($records1);
        $records2 = array_reverse($records2);
        $ar[0]['name'] = '10 Year Swap';
        $ar[0]['data'] = $records;
        $ar[1]['name'] = '1 Month Libor';
        $ar[1]['data'] = $records1;
        $ar[2]['name'] = '1 Year Libor';
        $ar[2]['data'] = $records2;
        $this->set('months', json_encode($months));
        $this->set('ar', json_encode($ar));
        $response['months'] = $months;
        $response['data'] = $ar;
        $fp = fopen('ratecharts.json', 'w');
//        $fp = fopen('monthcharts.json', 'w');
        fwrite($fp, json_encode($response));
        fclose($fp);
        echo "success";
        exit;
    }

    public function company_get_default_trend_data($swap_option = null, $filter_option = null) {
        $this->layout = 'ajax';
        $this->loadModel('Loanratesheet');
        $loanratesheet = $this->Loanratesheet->find('first', array(
            'order' => 'sheet_date DESC'
                )
        );
        $this->set('loanratesheet', $loanratesheet);
        if ($this->request->data) {
//            pr($this->request->data);
            if (isset($swap_option) and ( !empty($swap_option))) {
                //one month libor
                if ($swap_option == "1ml") {
                    $api_link = "USD1MTD156N";
                    $name = "1 Month Libor";
                }
                // 12 months libor
                elseif ($swap_option == "12ml") {
                    $api_link = "USD12MD156N";
                    $name = "12 months libor";
                }
            }
            if (isset($filter_option) and ( !empty($filter_option))) {
                if ($filter_option == "3m") {
                    $collapse = "monthly";
                    $limit = "3";
                } elseif ($filter_option == "6m") {
                    $collapse = "monthly";
                    $limit = "6";
                } elseif ($filter_option == "1y") {
                    $collapse = "monthly";
                    $limit = "12";
                } elseif ($filter_option == "5y") {
                    $collapse = "yearly";
                    $limit = "5";
                } elseif ($filter_option == "10y") {
                    $collapse = "yearly";
                    $limit = "10";
                }
            }
        } else {
            //default 10 years swap with 1 month data
            $name = "10 Year Swap";
            $api_link = "DSWP10";
            $collapse = "daily";
            $limit = "30";
        }
        $dataLink = "https://www.quandl.com/api/v3/datasets/FRED/" . $api_link . ".json?api_key=xGv2Mk5o8rauTs4TN8z_&order=desc&collapse=" . $collapse . "&limit=" . $limit;
        $data = $this->httpGet($dataLink);
        $dataList = json_decode($data);
        foreach ($dataList->dataset->data as $key => $val) {
            $month = date('M d, Y', strtotime($val[0]));
            $months[] = $month;
            $records[] = $val[1];
        }$ar = array();
        $months = array_reverse($months);
        $records = array_reverse($records);
        $ar[0]['name'] = $name;
        $ar[0]['data'] = $records;
        $this->set('months', json_encode($months));
        $this->set('ar', json_encode($ar));
        $this->set('dataList', $dataList);
    }

    public function company_gettrends() {
        //pr($this->request->data); exit;
        $this->layout = 'ajax';
        $this->loadModel('Loanratesheet');
        $loanratesheet = $this->Loanratesheet->find('first', array(
            'order' => 'sheet_date DESC'
                )
        );
        $this->set('loanratesheet', $loanratesheet);
        if ($this->request->data) {
          //  pr($this->request->data);
            if (isset($this->request->data['swapOption']) and ( !empty($this->request->data['swapOption']))) {
                $swap_option = $this->request->data['swapOption'];
                //one month libor
                if ($swap_option == "1ml") {
                    $api_link = "USD1MTD156N";
                    $name = "1 Month Libor";
                }
                // 12 months libor
                elseif ($swap_option == "12ml") {
                    $api_link = "USD12MD156N";
                    $name = "12 months libor";
                } elseif ($swap_option == "10ys") {
                    $api_link = "DSWP10";
                    $name = "10 Year Swap";
                }
            }
            if (isset($this->request->data['filterOption']) and ( !empty($this->request->data['filterOption']))) {
                $filter_option = $this->request->data['filterOption'];
                if ($filter_option == "3m") {
                    $collapse = "monthly";
                    $limit = "3";
                } elseif ($filter_option == "6m") {
                    $collapse = "monthly";
                    $limit = "6";
                } elseif ($filter_option == "1y") {
                    $collapse = "monthly";
                    $limit = "12";
                } elseif ($filter_option == "5y") {
                    $collapse = "yearly";
                    $limit = "5";
                } elseif ($filter_option == "10y") {
                    $collapse = "yearly";
                    $limit = "10";
                } else {
                    $collapse = "daily";
                    $limit = "30";
                }
            }
            $dataLink = "https://www.quandl.com/api/v3/datasets/FRED/" . $api_link . ".json?api_key=xGv2Mk5o8rauTs4TN8z_&order=desc&collapse=" . $collapse . "&limit=" . $limit;
            if ((!empty($this->request->data['fromDate'])) or ( !empty($this->request->data['toDate']))) {
                 $limit = "";
                if ((isset($this->request->data['fromDate'])) and ( !empty($this->request->data['fromDate']))) {
                    
                    $fromDate = strtotime($this->request->data['fromDate']);
                }
                
                if ((isset($this->request->data['toDate'])) and ( !empty($this->request->data['toDate']))) {
                    $toDate = strtotime($this->request->data['toDate']);
                    
                } else {
                    $toDate = strtotime(date("m/d/Y", time()));
                }
               $start_date=date("Y-m-d",$fromDate);
                 $end_date=date("Y-m-d",$toDate);
               
                $datediff = $toDate - $fromDate;
                $days = floor($datediff / (60 * 60 * 24));
                $year1 = date('Y', $fromDate);
                $year2 = date('Y', $toDate);
                $month1 = date('m', $fromDate);
                $month2 = date('m', $toDate);
                 $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);
                  if ($monthDiff > 12) {
                     $collapse = "yearly";
                } else {
                    $collapse = "monthly";
                }
                $dataLink = "https://www.quandl.com/api/v3/datasets/FRED/" . $api_link . ".json?api_key=xGv2Mk5o8rauTs4TN8z_&order=desc&collapse=" . $collapse . "&start_date=" . $start_date. "&end_date=". $end_date;
            }
        }
         $data = $this->httpGet($dataLink);
        $dataList = json_decode($data);
       // pr($dataList);
         //die;
//        $months=array();
//        $ar=array();
//        $dataList=array();
       if(!empty($dataList->dataset->data)){
        foreach ($dataList->dataset->data as $key => $val) {
            $month = date('M d, Y', strtotime($val[0]));
            $months[] = $month;
            $records[] = $val[1];
        }$ar = array();
        $months = array_reverse($months);
        $records = array_reverse($records);
        $ar[0]['name'] = $name;
        $ar[0]['data'] = $records;
        $this->set('months', json_encode($months));
        $this->set('ar', json_encode($ar));
        $this->set('dataList', $dataList);
       }
       
      
 }

}
