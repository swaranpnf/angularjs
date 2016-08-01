<?php

class LendersController extends AppController {

    var $name = 'Lender';
    var $uses = array('Lender');

    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow(array());
//        pr($_SERVER['HTTP_REFERER']);
    }

    public function company_index() {
        $this->layout = 'ajax';
        $lenders = $this->Lender->find('all', array('conditions' => array('user_id' => $_SESSION['Auth']['User']['id'])));
        $this->set('lenders', $lenders);
    }

    public function company_add() {
        $this->layout = 'ajax';
        if (isset($this->request->data) && !empty($this->request->data)) {
            $this->Lender->save($this->request->data);
            $id = $this->Lender->getLastInsertID();
            if (!empty($id)) {
                $response = array('status' => true, 'id' => $id, 'name' => $this->request->data['Lender']['name']);
            } else {
                $response = array('status' => false, 'id' => false);
            }
            echo json_encode($response);
            exit;
        }
    }

    public function company_updatestatus($model, $id) {

        $this->layout = 'ajax';
        $status = $_POST['status'];
        $statuschanged = $this->updateRecord($model, $id, $status);
        if ($statuschanged) {
            $response = array('status' => true, 'updated' => $status);
        } else {
            $response = array('status' => false);
        }
        echo json_encode($response);
        exit;
    }

    public function company_edit($id) {
        $this->layout = 'ajax';
        $lender = $this->Lender->findById($id);
        
        
        if (isset($this->request->data) && !empty($this->request->data)) {
            if (isset($this->request->data['Lender']['logo']['name']) && !empty($this->request->data['Lender']['logo']['name'])) {
                $logo = time() . "_" . $this->request->data['Lender']['logo']['name'];
                move_uploaded_file($this->request->data['Lender']['logo']['tmp_name'], "upload/Lenders/" . $logo);
                
                $data = array(
                    'file'=>"upload/Lenders/".$logo,
                    'width'=>200,
                    'height'=>120,
                    'output'=>"upload/Lenders/thumbs/",
                );
                $this->Qimage->resize($data);
                
                $this->request->data['Lender']['logo'] = $logo;
                $this->Lender->save($this->request->data);
                $response = array('status' => true);
                echo json_encode($response);
                exit;
            } else {

                $this->Lender->save($this->request->data);
                $response = array('status' => true);
                echo json_encode($response);
                exit;
            }
        }
        
        $this->loadModel('State');
        $states = $this->State->find('all');
        $this->set('states', $states);
//        pr($lender);

        // get all contacts
        $this->loadModel('LenderContact');
        $contacts = $this->LenderContact->find('all', array('conditions' => array('LenderContact.lender_id' => $id)));
        $this->set('contacts', $contacts);


        // get all contact mortgagee
        $this->loadModel('LenderMortgageContacts');
        $mortgagecontacts = $this->LenderMortgageContacts->find('all', array('conditions' => array('LenderMortgageContacts.lender_id' => $id)));
        $this->set('mortgagecontacts', $mortgagecontacts);


        // get all notices
        $this->loadModel('LenderNotice');
        $notices = $this->LenderNotice->find('all', array('conditions' => array('LenderNotice.lender_id' => $id)));
        $this->set('notices', $notices);


        // get all tips
        $this->loadModel('LenderTip');
        $tips = $this->LenderTip->find('all', array('conditions' => array('LenderTip.lender_id' => $id)));
        $this->set('tips', $tips);


        // get all Notes
        $this->loadModel('LenderNote');
        $notes = $this->LenderNote->find('all', array('conditions' => array('LenderNote.lender_id' => $id)));
        $this->set('notes', $notes);


        // get all Documents
        $this->loadModel('LenderDocument');
        $documents = $this->LenderDocument->find('all', array('conditions' => array('LenderDocument.lender_id' => $id)));
        $this->set('documents', $documents);


        $this->set('lender', $lender);
    }

    public function company_addfee() {
        $this->layout = 'ajax';
    }

    public function company_addcontact() {
        $this->layout = 'ajax';

        $this->loadModel('State');
        $this->loadModel('LenderContact');
        $states = $this->State->find('all');
        $this->set('states', $states);
        $this->set('lenderid', $_GET['lender_id']);

        if (isset($this->request->data) && !empty($this->request->data)) {

            if (isset($this->request->data['LenderContact']['states'])) {
                $this->request->data['LenderContact']['states'] = json_encode($this->request->data['LenderContact']['states']);
            }
            $this->LenderContact->save($this->request->data);
            $id = $this->LenderContact->getLastInsertID();

            if (!empty($id)) {
                $webroot = $this->webroot;

                $dlinks = $webroot . "company/lenders/deletecontact/" . $id;
                $delImage = $webroot . 'images/trash.png';
                $delLink = "<a href=\"javascript:\" ng-click=\"deleteRecords('lenderContactUnit$id', '$dlinks')\" ><img src='$delImage' ><span class='icons_cls'>Trash</span></a>";

                $elinks = $webroot . "company/lenders/editcontact/" . $id;
                $editImage = $webroot . 'images/edit.png';
                $editLink = "<a ng-click=\"openPopUp('get','$elinks','new_lender ad_cntcts')\" href='javascript:'><img src='$editImage' > <span class='icons_cls'>Edit</span></a>";

                $data = '<tr id="lenderContactUnit' . $id . '"> 
                    <td>' . $this->request->data['LenderContact']['firstname'] . ' ' . $this->request->data['LenderContact']['lastname'] . '</td>
                    <td>' . $this->request->data['LenderContact']['types'] . '</td>
                    <td>' . $this->request->data['LenderContact']['title'] . '</td>
                    <td>' . $this->request->data['LenderContact']['cellphone'] . '</td>
                    <td class="text-center cus_pro_td">' . $editLink . '</td>
                    <td class="text-center cus_pro_td">' . $delLink . '</td>
                </tr>';
                $response = array('status' => true, 'data' => $data);
            } else {
                $response = array('status' => false, 'data' => false);
            }
            echo json_encode($response);
            exit;
        }
    }

    public function company_editcontact($id) {
        $this->layout = 'ajax';

        $this->loadModel('State');
        $this->loadModel('LenderContact');
        $states = $this->State->find('all');
        $lendercontact = $this->LenderContact->findById($id);
        $this->set('states', $states);
        $this->set('lendercontact', $lendercontact);
        //$this->set('lenderid', $_GET['lender_id']);

        if (isset($this->request->data) && !empty($this->request->data)) {
            if (isset($this->request->data['LenderContact']['states'])) {
                $this->request->data['LenderContact']['states'] = json_encode($this->request->data['LenderContact']['states']);
            }
            $this->LenderContact->save($this->request->data);

            if (!empty($id)) {
                $lendercontact = $this->LenderContact->findById($id);
                $response = array('status' => true, 'data' => $lendercontact['LenderContact']);
            } else {
                $response = array('status' => false, 'data' => false);
            }
            echo json_encode($response);
            exit;
        }
    }

    public function company_deletecontact($id = null) {
        $this->layout = 'ajax';
        $this->loadModel('LenderContact');
        if ($id != null) {
            $this->LenderContact->delete($id);
            $response = array('status' => true);
        } else {
            $response = array('status' => false);
        }
        echo json_encode($response);
        exit;
    }

    public function company_addmortgagecontact() {
        $this->layout = 'ajax';

        $this->loadModel('State');
        $this->loadModel('LenderMortgageContact');
        $states = $this->State->find('all');
        $this->set('states', $states);
        $this->set('lenderid', $_GET['lender_id']);

        if (isset($this->request->data) && !empty($this->request->data)) {

            if (isset($this->request->data['LenderMortgageContact']['states'])) {
                $this->request->data['LenderMortgageContact']['states'] = json_encode($this->request->data['LenderMortgageContact']['states']);
            }
            $this->LenderMortgageContact->save($this->request->data);
            $id = $this->LenderMortgageContact->getLastInsertID();
            if (!empty($id)) {
                $webroot = $this->webroot;
                $dlinks = $webroot . "company/lenders/deletemortgagecontact/" . $id;
                $delImage = $webroot . 'images/trash.png';
                $delLink = "<a href=\"javascript:\" ng-click=\"deleteRecords('lenderMortgageContactUnit$id', '$dlinks')\" ><img src='$delImage' ><span class='icons_cls'>Trash</span></a>";

                $elinks = $webroot . "company/lenders/editmortgagecontact/" . $id;
                $editImage = $webroot . 'images/edit.png';
                $editLink = "<a ng-click=\"openPopUp('get','$elinks','new_lender ad_cntcts')\" href='javascript:'><img src='$editImage' > <span class='icons_cls'>Edit</span></a>";


                $data = '<tr id="lenderMortgageContactsUnit' . $id . '"> 
                    <td> <a href="#" class="fullname">' . $this->request->data['LenderMortgageContact']['fullname'] . '</a></td>
                    <td class="text-center cus_pro_td">' . $editLink . '</td>
                    <td class="text-center cus_pro_td">' . $delLink . '</td>
                </tr>';
                $response = array('status' => true, 'data' => $data);
            } else {
                $response = array('status' => false, 'data' => false);
            }
            echo json_encode($response);
            exit;
        }
        
        
    }

    public function company_editmortgagecontact($id) {
        $this->layout = 'ajax';

        $this->loadModel('State');
        $this->loadModel('LenderMortgageContact');
        $states = $this->State->find('all');
        $lendercontact = $this->LenderMortgageContact->findById($id);

        $this->set('states', $states);
        $this->set('lendermortagecontact', $lendercontact);

        if (isset($this->request->data) && !empty($this->request->data)) {
            if (isset($this->request->data['LenderMortgageContact']['states'])) {
                $this->request->data['LenderMortgageContact']['states'] = json_encode($this->request->data['LenderMortgageContact']['states']);
            }
            $saved = $this->LenderMortgageContact->save($this->request->data);

            if ($saved) {
                $lendercontact = $this->LenderMortgageContact->findById($id);
                $response = array('status' => true, 'data' => $lendercontact['LenderMortgageContact']);
            } else {
                $response = array('status' => false, 'data' => false);
            }
            echo json_encode($response);
            exit;
        }
    }

    public function company_deletemortgagecontact($id = null) {
        $this->layout = 'ajax';
        $this->loadModel('LenderMortgageContact');
        if ($id != null) {
            $this->LenderMortgageContact->delete($id);
            $response = array('status' => true);
        } else {
            $response = array('status' => false);
        }
        echo json_encode($response);
        exit;
    }

    public function company_addnotice() {
        $this->layout = 'ajax';

        $this->loadModel('State');
        $this->loadModel('LenderNotice');
        $states = $this->State->find('all');
        $this->set('states', $states);
        $this->set('lenderid', $_GET['lender_id']);

        if (isset($this->request->data) && !empty($this->request->data)) {
            $this->LenderNotice->save($this->request->data);
            $id = $this->LenderNotice->getLastInsertID();

            if (!empty($id)) {
                $webroot = $this->webroot;

                $dlinks = $webroot . "company/lenders/deletenotice/" . $id;
                $delImage = $webroot . 'images/trash.png';
                $delLink = "<a href=\"javascript:\" ng-click=\"deleteRecords('lenderNoticeUnit$id', '$dlinks')\" ><img src='$delImage' ><span class='icons_cls'>Trash</span></a>";

                $elinks = $webroot . "company/lenders/editnotice/" . $id;
                $editImage = $webroot . 'images/edit.png';
                $editLink = "<a ng-click=\"openPopUp('get','$elinks','new_lender ad_cntcts')\" href='javascript:'><img src='$editImage' > <span class='icons_cls'>Edit</span></a>";

                $data = '<tr id="lenderNoticeUnit' . $id . '"> 
                    <td class="date">' . date('M d, Y', time()) . ' </td>
                    <td class="subject">' . $this->request->data['LenderNotice']['subject'] . '</td>
                    <td class="text-center cus_pro_td">' . $editLink . '</td>
                    <td class="text-center cus_pro_td">' . $delLink . '</td>
                </tr>';

                $response = array('status' => true, 'data' => $data, 'id' => $id);
            } else {
                $response = array('status' => false, 'data' => false);
            }
            echo json_encode($response);
            exit;
        }
    }

    public function company_editnotice($id) {
        $this->layout = 'ajax';
        $this->loadModel('LenderNotice');
        $lendernotice = $this->LenderNotice->findById($id);
        $this->set('lendernotice', $lendernotice);
        if (isset($this->request->data) && !empty($this->request->data)) {
            $this->LenderNotice->save($this->request->data);
            if (!empty($id)) {
                $lendernotice = $this->LenderNotice->findById($id);
                $lendernotice['LenderNotice']['created'] = date('M d, Y', strtotime($lendernotice['LenderNotice']['created']));
                $response = array('status' => true, 'data' => $lendernotice['LenderNotice']);
            } else {
                $response = array('status' => false, 'data' => false);
            }
            echo json_encode($response);
            exit;
        }
    }

    public function company_deletenotice($id = null) {
        $this->layout = 'ajax';
        $this->loadModel('LenderNotice');
        if ($id != null) {
            $this->LenderNotice->delete($id);
            $response = array('status' => true);
        } else {
            $response = array('status' => false);
        }
        echo json_encode($response);
        exit;
    }

    public function company_addtips() {
        $this->layout = 'ajax';
        $this->loadModel('LenderTip');
        $this->set('lenderid', $_GET['lender_id']);
        if (isset($this->request->data) && !empty($this->request->data)) {
            $this->LenderTip->save($this->request->data);
            $id = $this->LenderTip->getLastInsertID();
            if (!empty($id)) {
                $webroot = $this->webroot;
                $dlinks = $webroot . "company/lenders/deletetips/" . $id;
                $elinks = $webroot . "company/lenders/edittips/" . $id;
                
                $delImage = $webroot . 'images/trash.png';
                $editImage = $webroot . 'images/edit.png';
                
                $editLink = "<a href=\"javascript:\" ng-click=\"openPopUp('get', '$elinks', 'new_lender ad_cntcts')\" ><img src='$editImage' ><span class='icons_cls'>Edit</span></a>";
                
                $delLink = "<a href=\"javascript:\" ng-click=\"deleteRecords('lenderTipsUnit$id', '$dlinks')\" ><img src='$delImage' ><span class='icons_cls'>Trash</span></a>";

                $data = '<tr id="lenderTipsUnit' . $id . '"> 
                    <td class="date">' . date('M d, Y', time()) . ' </td>
                    <td class="message">' . $this->request->data['LenderTip']['message'] . '</td>
                   <td class="text-center cus_pro_td">' . $editLink . '</td>
                   <td class="text-center cus_pro_td">' . $delLink . '</td>
                </tr>';

                $response = array('status' => true, 'data' => $data, 'id' => $id);
            } else {
                $response = array('status' => false, 'data' => false);
            }
            echo json_encode($response);
            exit;
        }
    }

    public function company_edittips($id) {
        $this->layout = 'ajax';
        $this->loadModel('LenderTip');
        $lendertips = $this->LenderTip->findById($id);
        $this->set('lendertips', $lendertips);
        if (isset($this->request->data) && !empty($this->request->data)) {
            $this->LenderTip->save($this->request->data);
            if (!empty($id)) {
                $lendertips = $this->LenderTip->findById($id);
                $lendertips['LenderTip']['created'] = date('M d, Y', strtotime($lendertips['LenderTip']['created']));
                $response = array('status' => true, 'data' => $lendertips['LenderTip']);
            } else {
                $response = array('status' => false, 'data' => false);
            }
            echo json_encode($response);
            exit;
        }
    }
    
    public function company_delete($id = null) {
        $this->layout = 'ajax';
        $this->loadModel('Lender');
        if ($id != null) {
            $this->Lender->delete($id);
            $response = array('status' => true);
        } else {
            $response = array('status' => false);
        }
        echo json_encode($response);
        exit;
    }

    public function company_deletetips($id = null) {
        $this->layout = 'ajax';
        $this->loadModel('LenderTip');
        if ($id != null) {
            $this->LenderTip->delete($id);
            $response = array('status' => true);
        } else {
            $response = array('status' => false);
        }
        echo json_encode($response);
        exit;
    }

    public function company_addnotes() {
        $this->layout = 'ajax';
        $this->loadModel('LenderNote');
        $this->set('lenderid', $_GET['lender_id']);
        if (isset($this->request->data) && !empty($this->request->data)) {
            $this->LenderNote->save($this->request->data);
            $id = $this->LenderNote->getLastInsertID();
            if (!empty($id)) {
                $webroot = $this->webroot;
                $dlinks = $webroot . "company/lenders/deletenotes/" . $id;
                $delImage = $webroot . 'images/trash.png';
                $delLink = "<a href=\"javascript:\" ng-click=\"deleteRecords('lenderNotesUnit$id', '$dlinks')\" ><img src='$delImage' ><span class='icons_cls'>Trash</span></a>";

                $elinks = $webroot . "company/lenders/editnotes/" . $id;
                $editImage = $webroot . 'images/edit.png';
                $editLink = "<a ng-click=\"openPopUp('get','$elinks','new_lender adnote')\" href='javascript:'><img src='$editImage' > <span class='icons_cls'>Edit</span></a>";

                $data = '<tr id="lenderNotesUnit' . $id . '"> 
                    <td class="date">' . date('M d, Y', time()) . ' </td>
                    <td class="tips">' . $this->request->data['LenderNote']['tips'] . '</td>
                    <td class="text-center cus_pro_td">' . $editLink . '</td>
                    <td class="text-center cus_pro_td">' . $delLink . '</td>
                </tr>';

                $response = array('status' => true, 'data' => $data, 'id' => $id);
            } else {
                $response = array('status' => false, 'data' => false);
            }
            echo json_encode($response);
            exit;
        }
    }

    public function company_editnotes($id) {
        $this->layout = 'ajax';
        $this->loadModel('LenderNote');
        $lendernotes = $this->LenderNote->findById($id);
        $this->set('lendernotes', $lendernotes);
        if (isset($this->request->data) && !empty($this->request->data)) {
            $this->LenderNote->save($this->request->data);
            if (!empty($id)) {
                $lendernotes = $this->LenderNote->findById($id);
                $lendernotes['LenderTip']['created'] = date('M d, Y', strtotime($lendernotes['LenderNote']['created']));
                $response = array('status' => true, 'data' => $lendernotes['LenderNote']);
            } else {
                $response = array('status' => false, 'data' => false);
            }
            echo json_encode($response);
            exit;
        }
    }

    public function company_deletenotes($id = null) {
        $this->layout = 'ajax';
        $this->loadModel('LenderNote');
        if ($id != null) {
            $this->LenderNote->delete($id);
            $response = array('status' => true);
        } else {
            $response = array('status' => false);
        }
        echo json_encode($response);
        exit;
    }

    public function company_adddocument() {
        $this->layout = 'ajax';
        $this->loadModel('LenderDocument');
        $this->set('lenderid', $_GET['lender_id']);
        if (isset($this->request->data) && !empty($this->request->data)) {
            if (isset($this->request->data['LenderDocument']['file']) && !empty($this->request->data['LenderDocument']['file'])) {
                $fname = time() . "_" . $this->request->data['LenderDocument']['file']['name'];
                $name = $this->request->data['LenderDocument']['name'];
                $size = $this->request->data['LenderDocument']['file']['size'];
                $type = $this->request->data['LenderDocument']['file']['type'];
                $ext = end(explode('.', $this->request->data['LenderDocument']['file']['name']));
                move_uploaded_file($this->request->data['LenderDocument']['file']['tmp_name'], "upload/Lenders/Documents/" . $fname);
                $this->request->data['LenderDocument']['size'] = $size;
                $this->request->data['LenderDocument']['types'] = $type;
                $this->request->data['LenderDocument']['file'] = $fname;
                $this->request->data['LenderDocument']['extension'] = $ext;
                $this->LenderDocument->save($this->request->data);
                $id = $this->LenderDocument->getLastInsertID();
                $webroot = $this->webroot;
                $dlinks = $webroot . "company/lenders/deletedocument/" . $id;
                $olink = $webroot . "company/lenders/opendocument/" . $id;
                
                $delImage = $webroot . 'images/trash.png';
                $delLink = "<a href=\"javascript:\" ng-click=\"deleteRecords('lenderDocumentUnit$id', '$dlinks')\" ><img src='$delImage' ><span class='icons_cls'>Trash</span></a>";

                $olinks = $webroot . "company/lenders/opendocument/" . $id;
                $openImage = $webroot . 'images/openfile.png';
                $openLink = "<a target='_blank' href='$olink'><img src='$openImage' > <span class='icons_cls'>Open File</span></a>";
                $fsize = $this->getSize($size);
                $data = '<tr id="lenderDocumentUnit' . $id . '"> 
                    <td class="date">' . date('M d, Y', time()) . ' </td>
                    <td class="name">' . $name . '</td>
                   <td class="size">' . $fsize . '</td>
                       <td class="size">' . $ext . '</td>
                    <td class="text-center cus_pro_td">' . $openLink . '</td>
                    <td class="text-center cus_pro_td">' . $delLink . '</td>
                </tr>';
                $response = array('status' => true, 'data' => $data);
                echo json_encode($response);
                exit;
            } else {
                $response = array('status' => false, 'data' => false);
                echo json_encode($response);
                exit;
            }
        }
    }

    public function company_opendocument($id) {
        $this->layout = 'ajax';
        $this->loadModel('LenderDocument');
        $document = $this->LenderDocument->findById($id);
        if ($document) {
            $fullpath = 'upload/Lenders/Documents/' . $document['LenderDocument']['file'];
            $type = $this->get_mime_content_type($fullpath);
            $size = $document['LenderDocument']['size'];
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

    public function company_deletedocument($id = null) {
        $this->layout = 'ajax';
        $this->loadModel('LenderDocument');
        $document = $this->LenderDocument->findById($id);
        if ($document) {
            $fullpath = 'upload/Lenders/Documents/' . $document['LenderDocument']['file'];
            $this->LenderDocument->delete($id);
            unlink($fullpath);
            $response = array('status' => true);
        } else {
            $response = array('status' => false);
        }
        echo json_encode($response);
        exit;
    }

    public function company_addfees() {
        $this->layout = 'ajax';
        $this->loadModel('Hud');
        $this->loadModel('Loanfee');
        $this->Hud->hasMany['Loanfee']['conditions']['user_id'] = $_SESSION['Auth']["User"]['id'];
        $this->Hud->hasMany['Loanfee']['fields'] = array('DISTINCT(Loanfee.hud)', 'Loanfee.*');
        $this->Hud->recursive = -1;
        $data = $this->Hud->find('list', array(
            'conditions' => array('Hud.id' => array('800', '900', '1100')),
            'fields' => array('Hud.id', 'Hud.name'),
        ));
        $dataList = array();
        $i = 0;
        foreach ($data as $key => $value) {
            $this->Loanfee->recursive = -1;
            $loanfee = $this->Loanfee->find('all', array(
                'conditions' => array('Loanfee.user_id' => $_SESSION['Auth']['User']['id'], 'Loanfee.hud_id' => $key),
                'group' => array('Loanfee.hud'),
            ));
            $dataList[$i]['Hud'] = array('id' => $key, 'value' => $value);
            $dataList[$i]['Loanfee'] = $loanfee;
            $i++;
        }
        $this->set('dataList', $dataList);

        $this->loadModel('LenderHudFees');
        $LenderHudFees = $this->LenderHudFees->find('list', array(
            'conditions' => array(
                'LenderHudFees.lender_id' => $_GET['lender_id']
            ),
            'fields' => array(
                'LenderHudFees.hud'
            ),
        ));
        $this->set('lenderid', $_GET['lender_id']);
        $this->set('LenderHudFees', $LenderHudFees);
    }

    public function company_insert($lenderid) {

        $this->layout = 'ajax';
        $this->loadModel('Loanfee');
        $this->loadModel('LenderHudFee');
        $this->Loanfee->recursive = -1;
        $LenderHudFees = $this->Loanfee->find('all', array(
            'conditions' => array(
                'Loanfee.id' => $_POST['data']['LenderHudFee']['id']
            )
        ));
//        pr($_POST['data']['LenderHudFee']['lender_id']); exit;
        $LenderHudFeesExist = $this->LenderHudFee->deleteAll(array(
            'LenderHudFee.lender_id' => $_POST['data']['LenderHudFee']['lender_id']
        ) , false);
        $data = array();
        foreach ($LenderHudFees as $key => $value) {
            
            $data['LenderHudFee'] = array(
                'user_id' => $_SESSION['Auth']['User']['id'],
                'lender_id' => $_POST['data']['LenderHudFee']['lender_id'],
                'is_edit' => ($value['Loanfee']['field'] === 'text') ? 1 : 0,
                'hud_id' => $value['Loanfee']['hud_id'],
                'hud' => $value['Loanfee']['hud'],
                'description' => $value['Loanfee']['value']
            );
            $this->LenderHudFee->create();
            $this->LenderHudFee->save($data);
            
//            $isExist = $this->LenderHudFee->find('first', array('conditions' => array(
//                    'lender_id' => $_POST['data']['LenderHudFee']['lender_id'],
//                    'hud' => $value['Loanfee']['hud']
//            )));
//            if (empty($isExist)) {
//                $data['LenderHudFee'] = array(
//                    'user_id' => $_SESSION['Auth']['User']['id'],
//                    'lender_id' => $_POST['data']['LenderHudFee']['lender_id'],
//                    'is_edit' => ($value['Loanfee']['field'] === 'text') ? 1 : 0,
//                    'hud_id' => $value['Loanfee']['hud_id'],
//                    'hud' => $value['Loanfee']['hud'],
//                    'description' => $value['Loanfee']['value']
//                );
//                $this->LenderHudFee->create();
//                $this->LenderHudFee->save($data);
//            }
            
            
        }
        echo json_encode(array('status' => true,'lenderid'=>$_POST['data']['LenderHudFee']['lender_id']));
        exit;
    }

    public function company_angular() {
        $this->layout = 'angular';
    }

    public function company_upload($model = null, $id = null) {
        $this->layout = 'ajax';
        $this->LoadModel($model);
        $this->LoadModel('LenderResource');
        if ($model == null || $id == null) {
            echo json_encode(array('status' => false));
            exit;
        }
        $data = $this->$model->findById($id);
        if ($data) {
            $fname = time() . "_" . $_FILES['file']['name'];
            $size = $_FILES['file']['size'];
            $type = $_FILES['file']['type'];
            $ext = end(explode('.', $_FILES['file']['name']));
            move_uploaded_file($_FILES['file']['tmp_name'], "upload/Lenders/Resources/" . $fname);
            $resource = array();
            switch ($model) {
                case 'LenderNotice':
                    $resource['LenderResource']['lender_notice_id'] = $id;
                    break;
                case 'LenderTip':
                    $resource['LenderResource']['lender_tip_id'] = $id;
                    break;
                case 'LenderNote':
                    $resource['LenderResource']['lender_note_id'] = $id;
                    break;
                default:
                    break;
            }
            $resource['LenderResource']['user_id'] = $_SESSION['Auth']["User"]['id'];
            $resource['LenderResource']['lender_id'] = $data[$model]['lender_id'];
            $resource['LenderResource']['size'] = $size;
            $resource['LenderResource']['types'] = $type;
            $resource['LenderResource']['file'] = $fname;
            $resource['LenderResource']['extension'] = $ext;
            $this->LenderResource->save($resource);
            $id = $this->LenderResource->getLastInsertID();
            echo json_encode(array('status' => true, 'id' => $id));
            exit;
        } else {
            echo json_encode(array('status' => false));
            exit;
        }
    }

    public function company_removeresources($id = null) {
        $this->layout = 'ajax';
        $this->loadModel('LenderResource');
        $document = $this->LenderResource->findById($id);
        if ($document) {
            $fullpath = 'upload/Lenders/Resources/' . $document['LenderResource']['file'];
            $this->LenderResource->delete($id);
            unlink($fullpath);
            $response = array('status' => true);
        } else {
            $response = array('status' => false);
        }
        echo json_encode($response);
        exit;
    }
    
    public function company_fullnote($id) {
        $this->layout = 'ajax';
        $this->loadModel('LenderNote');
        $notes = $this->LenderNote->findById($id);
        $this->set('notes' , $notes);
    }

}
