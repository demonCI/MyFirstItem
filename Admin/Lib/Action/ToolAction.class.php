<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class ToolAction extends CommonAction{
    public function index(){
        echo  111;
        exit;
        $model = D('order');
        $map = $this->_search($model);
        $map['order_type']=2;
        $_SESSION['map'] = $map;
        if (!empty($model)) {
            $this->_list($model, $map);
        }
        $this->display();
        return;
    }


    public function excel() {
        $map = $_SESSION['map'];
        $list = D('order')->where($map)->select();
        $title = array('店铺名称', '工程名称', '申请类型', '申请描述', '申请时间', '施工人员名称', '施工人员电话', '订单状态');
        $rs = array(); //数据库获取数据
        $this->export_execl($title, $list);
        $this->success('操作成功');
    }

    public function export_execl($title, $rs) {
        import('@.ORG.Excel.PHPExcel');
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
                ->setLastModifiedBy("Maarten Balliauw")
                ->setTitle("Office 2007 XLSX Test Document")
                ->setSubject("Office 2007 XLSX Test Document")
                ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
                ->setKeywords("office 2007 openxml php")
                ->setCategory("Test result file");
        $col = 2;
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', $title['0'])
                ->setCellValue('B1', $title['1'])
                ->setCellValue('C1', $title['2'])
                ->setCellValue('D1', $title['3'])
                ->setCellValue('E1', $title['4'])
                ->setCellValue('F1', $title['5'])
                ->setCellValue('G1', $title['6'])
                ->setCellValue('H1', $title['7']);
        $objPHPExcel->setActiveSheetIndex(0);
        foreach ($rs as $k => $v) {
            $objPHPExcel->setActiveSheetIndex(0)
//                    ->setCellValue('C' . $col, $express)
//                    ->setCellValue('E' . $col, getFieldById($v['Village'], 'Village', $title = 'name'))
//                    ->setCellValue('H' . $col, getCarflago($v['overdraft']))
                    ->setCellValue('A' . $col, getFieldById($v['shop_id'], 'shop', $title = 'name'))
                    ->setCellValue('B' . $col, getFieldById($v['project_id'], 'project', $title = 'name'))
                    ->setCellValue('C' . $col, $v['type'])
                    ->setCellValue('D' . $col, $v['reason'])
                    ->setCellValue('E' . $col, $v['ptime'])
                    ->setCellValue('F' . $col, $v['project_name'])
                    ->setCellValue('G' . $col, $v['project_title'])
                    ->setCellValue('H' . $col, $v['is_del']);
            $col++;
        }
        $objPHPExcel->getActiveSheet()->setTitle('订单表导出'); //设置sheet标签的名称
        $objPHPExcel->setActiveSheetIndex(0);
        ob_end_clean();  //清空缓存
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename=订单表导出.xls'); //设置文件的名称
        header("Content-Transfer-Encoding:binary");
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    public function pdf() {
        $map = $_SESSION['map'];
        $list = D('order')->where($map)->field('shop_id,project_id,order_type,ptime,reason,project_name,project_phone,is_del')->select();
        $result = array();
        if (!empty($list)) {
            $rs = array();
            foreach ($list as $key => $value) {
                $rs['shop_id'] = getFieldById($value['shop_id'], 'shop', 'name');
                $rs['project_id'] = getFieldById($value['project_id'], 'project', 'name');
                $result = $rs[];
            }
        }
        $title = array('店铺名称', '工程名称', '申请类型', '申请描述', '申请时间', '施工人员名称', '施工人员电话', '订单状态');
        $this->export_pdf($title, $list);
        $this->success('操作成功');
    }

    public function export_pdf($header = array(), $data = array(), $fileName = 'Newfile') {
        set_time_limit(120);
        if (empty($header) || empty($data))
            $this->error("导出的数据为空！");
        Vendor("tcpdf.tcpdf");
        require_cache(VENDOR_PATH . 'tcpdf/examples/lang/eng.php');
        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false); //新建pdf文件
        //设置文件信息
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor("Author");
        $pdf->SetTitle("pdf test");
        $pdf->SetSubject('TCPDF Tutorial');
        $pdf->SetKeywords('TCPDF, PDF, example, test, guide');
        //设置页眉页脚
//        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, 'www.thinkphp.com', 'Copyright © 2014-2015 by xxx, Ltd. All Rights reserved', array(66, 66, 66), array(0, 0, 0));
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED); //设置默认等宽字体
        $pdf->SetMargins(PDF_MARGIN_LEFT, 24, PDF_MARGIN_RIGHT); //设置页面边幅
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM); //设置自动分页符
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        Log::write('111111', Log::INFO);
        $pdf->setLanguageArray($l);
        Log::write('33333', Log::INFO);
        $pdf->SetFont('droidsansfallback', '');
        $pdf->AddPage();

        $pdf->SetFillColor(245, 245, 245);
        $pdf->SetTextColor(0);
        $pdf->SetDrawColor(66, 66, 66);
        $pdf->SetLineWidth(0.3);
        $pdf->SetFont('droidsansfallback', '', 9);
        // Header
        $num_headers = count($header);
        for ($i = 0; $i < $num_headers; ++$i) {
            $pdf->Cell(180 / $num_headers, 8, $header[$i], 1, 0, 'C', 1);
        }
        $pdf->Ln();

        // 填充数据
        $fill = 0;
        foreach ($data as $list) {
            //每頁重复表格标题行
            if (($pdf->getPageHeight() - $pdf->getY()) < ($pdf->getBreakMargin() + 2)) {
                $pdf->SetFillColor(245, 245, 245);
                $pdf->SetTextColor(0);
                $pdf->SetDrawColor(66, 66, 66);
                $pdf->SetLineWidth(0.3);
                $pdf->SetFont('droidsansfallback', '', 9);
                // Header
                for ($i = 0; $i < $num_headers; ++$i) {
                    $pdf->Cell(180 / $num_headers, 8, $header[$i], 1, 0, 'C', 1);
                }
                $pdf->Ln();
            }
            // Color and font restoration
            $pdf->SetFillColor(245, 245, 245);
            $pdf->SetTextColor(40);
            $pdf->SetLineWidth(0.1);
            $pdf->SetFont('droidsansfallback', '');
            //$pdf->SetFont('stsongstdlight', '', 12);
            foreach ($list as $key => $row) {
                Log::write($row, Log::INFO);
                //$pdf->Cell($width, 6, $row, 'LR', 0, 'C', $fill);
                $pdf->MultiCell(180 / $num_headers, 6, $row, $border = 1, $align = 'C', $fill, $ln = 0, $x = '', $y = '', $reseth = true, $stretch = 0, $ishtml = false, $autopadding = true, $maxh = 0, $valign = 'C', $fitcell = true);
            }
            $pdf->Ln();
            $fill = !$fill;
        }
        $showType = 'D'; //PDF输出的方式。I，在浏览器中打开；D，以文件形式下载；F，保存到服务器中；S，以字符串形式输出；E：以邮件的附件输出。
        ob_end_clean();
        $pdf->Output("{$fileName}.pdf", $showType);
        exit;
    }

}
