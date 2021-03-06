<?php
    //
    // vnStat PHP frontend (c)2006-2010 Tawan Saiphan (paball2589@gmail.com)
    //
    // This program is free software; you can redistribute it and/or modify
    // it under the terms of the GNU General Public License as published by
    // the Free Software Foundation; either version 2 of the License, or
    // (at your option) any later version.
    //
    // This program is distributed in the hope that it will be useful,
    // but WITHOUT ANY WARRANTY; without even the implied warranty of
    // MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    // GNU General Public License for more details.
    //
    // You should have received a copy of the GNU General Public License
    // along with this program; if not, write to the Free Software
    // Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
    //
    //
    // see file COPYING or at http://www.gnu.org/licenses/gpl.html 
    // for more information.
    //
    require 'config.php';
    require 'localize.php';
    require 'vnstat.php';

    validate_input();

    require "./themes/$style/theme.php";

    function write_side_bar()
    {
        global $iface, $page, $graph, $script, $style;
        global $iface_list, $iface_title;   
        global $page_list, $page_title;
        
        $p = "&amp;graph=$graph&amp;style=$style";

        print "<ul class=\"iface\">\n";
        foreach ($iface_list as $if)
        {
            print "<li class=\"iface\">";
            if (isset($iface_title[$if]))
            {
                print $iface_title[$if];
            }
            else
            {
                print $if;
            }
            print "<ul class=\"page\">\n";
            foreach ($page_list as $pg)
            {
                print "<li class=\"page\"><a href=\"$script?if=$if$p&amp;page=$pg\">".$page_title[$pg]."</a></li>\n";
            }
            print "</ul></li>\n";
	    
        }
        print "</ul>\n"; 
    }
    

    function kbytes_to_string($kb)
    {
        $units = array('TB','GB','MB','KB');
        $scale = 1024*1024*1024;
        $ui = 0;

        while (($kb < $scale) && ($scale > 1))
        {
            $ui++;
            $scale = $scale / 1024;
        }   
        return sprintf("%0.2f %s", ($kb/$scale),$units[$ui]);
    }
    
    function write_summary()
    {
        global $summary,$top,$day,$hour,$month;

        $trx = $summary['totalrx']*1024+$summary['totalrxk'];
        $ttx = $summary['totaltx']*1024+$summary['totaltxk'];

        //
        // build array for write_data_table
        //
        $sum[0]['act'] = 1;
        $sum[0]['label'] = T('This hour');
        $sum[0]['rx'] = $hour[0]['rx'];
        $sum[0]['tx'] = $hour[0]['tx'];

        $sum[1]['act'] = 1;
        $sum[1]['label'] = T('This day');
        $sum[1]['rx'] = $day[0]['rx'];
        $sum[1]['tx'] = $day[0]['tx'];

        $sum[2]['act'] = 1;
        $sum[2]['label'] = T('This month');
        $sum[2]['rx'] = $month[0]['rx'];
        $sum[2]['tx'] = $month[0]['tx'];

        $sum[3]['act'] = 1;
        $sum[3]['label'] = T('All time');
        $sum[3]['rx'] = $trx;
        $sum[3]['tx'] = $ttx;

        write_data_table(T('Summary'), $sum);
        print "<br/>\n";
        write_data_table(T('Top 10 days'), $top);
    }
    
    
    function write_data_table($caption, $tab)
    {
		print "<center>";
        print "<table width=\"600\" cellspacing=\"0\">\n";
        print "<caption>$caption</caption>\n";
        print "<tr>";
        print "<th class=\"label\" style=\"width:120px;\">&nbsp;</th>";
        print "<th class=\"label\">".T('In')."</th>";
        print "<th class=\"label\">".T('Out')."</th>";
        print "<th class=\"label\">".T('Total')."</th>";  
        print "</tr>\n";
		
        for ($i=0; $i<count($tab); $i++)
        {
            if ($tab[$i]['act'] == 1)
            {
                $t = $tab[$i]['label'];
                $rx = kbytes_to_string($tab[$i]['rx']);
                $tx = kbytes_to_string($tab[$i]['tx']);
                $total = kbytes_to_string($tab[$i]['rx']+$tab[$i]['tx']);
                $id = ($i & 1) ? 'odd' : 'even';
                print "<tr>";
                print "<td class=\"label_$id\">$t</td>";
                print "<td class=\"numeric_$id\">$rx</td>";
                print "<td class=\"numeric_$id\">$tx</td>";
                print "<td class=\"numeric_$id\">$total</td>";
                print "</tr>\n";
             }
        }
        print "</table>\n";
		print "</center>";
    }

    get_vnstat_data();

    //
    // html start
    //
    header('Content-type: text/html; charset=utf-8');
    print '<?xml version="1.0"?>';
?>        

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
  <title>ส่วนจัดการและแสดงผลข้อมูลเซิพเวอร์</title>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <style type="text/css">
<!--
-->
body {background:#242424; font-family:Verdana; font-size:16px;}
#wrap { background:#242424; padding:10px; margin:0 auto; border:1px solid #474747;}
#sidebar {width: 160px; float: left; padding: 3px 4px; color: #fff; background-color: #2F2F2F; border:1px solid #474747; -moz-border-radius:8px;}
#sidebar ul.iface {}
#sidebar li.iface {list-style-type:none; color:#FF0000; text-transform:uppercase; padding-bottom:10px; text-align:center;}
#sidebar a{color:#aaa;}
#sidebar ul.page {}
#sidebar li.page {list-style-type:none; text-transform:none;}

#header {padding: 3px; color: #fff; background-color: #2F2F2F; text-align: center; border:1px solid #474747; font-size:14px; font-weight:bold; -moz-border-radius:8px;}
#footer {padding: 3px; color: #fff; background-color: #2F2F2F; text-align: center; border:1px solid #474747; font-size:11px; -moz-border-radius:8px; clear:both; margin-top:10px;}
#footer a {color:#fff;}
#main {padding: 10px 10px 10px 10px; color: #fff; background-color: #2F2F2F; text-align: center; border:1px solid #474747; -moz-border-radius:8px; margin-top:10px;}
#main td {padding:1px 0;}
#main td.numeric_odd {text-align: right; color: #fff; background:#474747;}
#main td.numeric_even {text-align: right; color: #fff; background:#242424;}
#main td.label_odd {color: #fff; background:#474747;}
#main td.label_even {color: #fff; background:#242424;}
#main th.label {color: #fff; padding:2px 0; border-bottom:1px solid #fff;}
#main caption {padding: 3px 0 4px 0; color:#7CFC00; text-transform:uppercase;}

<!--
.style1 {color: #FFFFFF}
-->
input
{
display:block;
border-radius:5px;
background: #00BFFF;
width:90%;
padding:12px 20px 12px 10px;
border:none;
color:#000000;                       
box-shadow:inset 0px 1px 5px #00FF00;
font-size:1.0em;
-webkit-transition:0.5s ease;
-moz-transition:0.5s ease;
-o-transition:0.5s ease;
-ms-transition:0.5s ease;
transition:0.5s ease; 
}
</style>
</head>
<body>

<div id="wrap">
  <div id="content">
  <div id="header">
    <img src="openvpn-as.png" />
<br /> 
       <?php $ip = getenv("SERVER_NAME") ; Echo "IP : " . $ip; ?> <font color="#00FF00">Port 1194,443 SSH 22,143 Proxy 3128,8080<br>
       
    <table width="400" border="0" align="center" cellpadding="0" cellspacing="0">
      <tr>
        <td width="20%"><form action="index.php?if=eth0&graph=large&style=dark&page=s" method="post" name="form2" id="form2">
            <div align="right">
              <input type="submit" name="Submit" value="โดยรวม" />
            </div>
        </form></td>
        <td width="20%"><form action="index.php?if=eth0&graph=large&style=dark&page=h" method="post" name="form3" id="form3">
            <div align="right">
              <input type="submit" name="Submit2" value="ชั่วโมง" />
            </div>
        </form></td>
        <td width="20%"><form action="index.php?if=eth0&graph=large&style=dark&page=d" method="post" name="form4" id="form4">
            <div align="right">
              <input type="submit" name="Submit3" value="รายวัน" />
            </div>
        </form></td>
		        <td width="20%"><form action="index.php?if=eth0&graph=large&style=dark&page=m" method="post" name="form4" id="form4">
            <div align="right">
              <input type="submit" name="Submit3" value="เดือน" />
            </div>
        </form></td>
				        <td width="20%">
            <div align="right">
              <input type="submit" name="Submit3" value="ดาวน์โหลดไฟล์" ONCLICK="window.location.href='http://<?PHP echo $ip;?>/client.php'"/>
            </div>
      <td width="20%">
            <div align="right">
                <input type="submit" name="Submit3" value="เข้าระบบ" onclick="window.location.href='href='http://<?PHP echo $ip;?>/client.php'"/>
      </tr>
    </table>
  <?php print T('Traffic data for')." $iface_title[$iface] ($iface)";?></div>
    <div id="main">
    <center>
          <table BORDERCOLOR="#99FF00" width="600" border="1" cellspacing="0" cellpadding="0">
            <tr>
              <td><center>
                <font color="#20B2AA">ผู้ดูแลระบบหากติดตั้งไฟล์ เชื่อมต่อไว้แล้วผู้ใช้ทั่วไปสามารถดาวน์โหลดไฟล์ไปใช้งานได้กับโปรแกรม OpenVPN, ชื่อผู้ใช้งานหรือรหัสผ่านกรุณาติดต่อเจ้าของเซิพนี้    <font color="#7CFC00">หากมีเปลี่ยนแปลงจะแจ้งทราบให้นะครับ!
              </center></td>
            </tr>
          </table>
      </center>
    <?php
    $graph_params = "if=$iface&amp;page=$page&amp;style=$style";
    if ($page != 's')
        if ($graph_format == 'svg') {
	     print "<object type=\"image/svg+xml\" width=\"692\" height=\"297\" data=\"graph_svg.php?$graph_params\"></object>\n";
        } else {
	     print "<img src=\"graph.php?$graph_params\" alt=\"graph\"/>\n";	
        }

    if ($page == 's')
    {
        write_summary();
    }
    else if ($page == 'h')
    {   
        write_data_table(T('Last 24 hours'), $hour); 
    }
    else if ($page == 'd')
    {
        write_data_table(T('Last 30 days'), $day);	
    }
    else if ($page == 'm')
    {
        write_data_table(T('Last 12 months'), $month);   
    }
    ?>
        <center>
          <table BORDERCOLOR="#33FFFF" width="600" border="1" cellspacing="0" cellpadding="0">
            <tr>
              <td><center>
                <font color="#D02090">นี้คือระบบ VPN ที่รันบน VPS 100% ด้วยระบบจัดการที่ทันสมัยรองรับการเชื่อมต่อทุกโปรแกรมบนอุปรกรณ์ มือถือและคอมพิวเตอร์ พร้อมด้วยระบบตั้งค่าความปลอดภัย ติดตั้งระบบได้ที่    เบอร์โทรศพท์> 0641297186 <a href="https://www.facebook.com/profile.php?id=100023629252280"><font color="#FF0033">คลิ๊ก!</font>
              </center></td>
            </tr>
          </table>
      </center>
    </div>
    <div id="footer">
	<p>Facebook : <a href="https://www.facebook.com/profile.php?id=100023629252280">Tawan Saiphan</a> Email : paball2589@gmail.com</p>
      <p><font color="#ADFF2F">&copy;2018-2025 ลิขสิทธิ์ VPNNM.TK Server โดย <a href="Facebook: Tawan Saiphan"><font color="#66FF33">VPNNM.TKSecurity</a><font color="#87CEFA">(ห้ามแก้ไขหน้าระบบโดยไม่ได้รับอนุณาติไม่งั้นไม่รับผิดขอบนะครับ!)</p>

    </div>
  </div>
</div>

</body></html>
