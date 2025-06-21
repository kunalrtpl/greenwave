<html>
    <head>
        <style type='text/css'>
                .style1 {
                    color: #FFFFFF
                }
                .style2 {
                    font-size: 11px;
                    font-weight: bold;
                    text-decoration: none;
                    font-family: Verdana, Arial, Helvetica, sans-serif;
                    color:#666666;
                }
                .style3 {
                    text-decoration: none;
                    font-family: Verdana, Arial, Helvetica, sans-serif;
                    font-size: 11px;
                    color:#666666;
                }
        </style>
    </head>
    <body>
        <table width='80%' border='0' cellpadding='3' cellspacing='3' style='border:#EFEFEF 5px solid; padding:5px;'>
            <tr>
                <td colspan='3'></td>
            </tr>
            <tr>
                <td  align='left' valign='middle'><img border='0' width="75px" src="{{asset('images/static/logo.png')}}" alt='logo' /></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td class='style2'>{{$heading}} :-</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>           
            <tr>
               <td align='left' valign='middle'>
                   <table width='98%' border='0' align='right' cellpadding='5' cellspacing='5' style='background-color:#F5F5F5'>
                        <tr>
                           <td width='30%' align='left' valign='top' class='style2'>Name:</td>
                           <td width='5%' align='left' valign='top' class='style2'>:</td>
                           <td width='65%' align='left' valign='top' class='style3'>{{ $data['name'] }}</td>
                        </tr>
                        <tr>
                           <td width='30%' align='left' valign='top' class='style2'>Email:</td>
                           <td width='5%' align='left' valign='top' class='style2'>:</td>
                           <td width='65%' align='left' valign='top' class='style3'>{{ $data['email'] }}</td>
                        </tr>
                        <tr>
                           <td width='30%' align='left' valign='top' class='style2'>Phone:</td>
                           <td width='5%' align='left' valign='top' class='style2'>:</td>
                           <td width='65%' align='left' valign='top' class='style3'>{{ $data['phone'] }}</td>
                        </tr>
                        <tr>
                           <td width='30%' align='left' valign='top' class='style2'>Address:</td>
                           <td width='5%' align='left' valign='top' class='style2'>:</td>
                           <td width='65%' align='left' valign='top' class='style3'>{{ $data['address'] }}</td>
                        </tr>
                        <tr>
                           <td width='30%' align='left' valign='top' class='style2'>Prodct Model:</td>
                           <td width='5%' align='left' valign='top' class='style2'>:</td>
                           <td width='65%' align='left' valign='top' class='style3'>{{ $productdetails['promodel']['model_name'] }}</td>
                        </tr>
                        <tr>
                           <td width='30%' align='left' valign='top' class='style2'>PCD:</td>
                           <td width='5%' align='left' valign='top' class='style2'>:</td>
                           <td width='65%' align='left' valign='top' class='style3'>{{ $productdetails['pcd'] }}</td>
                        </tr>
                        <tr>
                           <td width='30%' align='left' valign='top' class='style2'>Holes:</td>
                           <td width='5%' align='left' valign='top' class='style2'>:</td>
                           <td width='65%' align='left' valign='top' class='style3'>{{ $productdetails['holes'] }}</td>
                        </tr>
                        <tr>
                           <td width='30%' align='left' valign='top' class='style2'>Inches:</td>
                           <td width='5%' align='left' valign='top' class='style2'>:</td>
                           <td width='65%' align='left' valign='top' class='style3'>{{ $productdetails['inches'] }}</td>
                        </tr>
                        <tr>
                           <td width='30%' align='left' valign='top' class='style2'>Width:</td>
                           <td width='5%' align='left' valign='top' class='style2'>:</td>
                           <td width='65%' align='left' valign='top' class='style3'>{{ $productdetails['width'] }}</td>
                        </tr>
                        <tr>
                           <td width='30%' align='left' valign='top' class='style2'>CB:</td>
                           <td width='5%' align='left' valign='top' class='style2'>:</td>
                           <td width='65%' align='left' valign='top' class='style3'>{{ $productdetails['cb'] }}</td>
                        </tr>
                        <tr>
                           <td width='30%' align='left' valign='top' class='style2'>Finish:</td>
                           <td width='5%' align='left' valign='top' class='style2'>:</td>
                           <td width='65%' align='left' valign='top' class='style3'>{{ $productdetails['finish'] }}</td>
                        </tr>
                        <tr>
                           <td width='30%' align='left' valign='top' class='style2'>ET:</td>
                           <td width='5%' align='left' valign='top' class='style2'>:</td>
                           <td width='65%' align='left' valign='top' class='style3'>{{ $productdetails['et'] }}</td>
                        </tr>
                   </table>
               </td>
           </tr>
           <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td  class='style2'> Regards<br />
                    Team {{config('constants.project_name')}} 
                </td>
            </tr>
        </table>
    </body>
</html>