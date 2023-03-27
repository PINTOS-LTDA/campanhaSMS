function Exportar()
{
    var textRange; var j=0;
    var tab = document.getElementById("Tabela"); // id da tabela
    if(document.getElementById("Tabela").rows.length < 2){
        alert("Por favor, realizar uma pesquisa antes de exportar.");
    }
    else{
        var a=[], csv='', LF='\r\n', r, c, rs, cs, row, cell, i, j, v;
     
        for (r=0; r<tab.rows.length; r++){
            row = tab.rows[r];
             
            for (c=0; c<row.cells.length; c++){
                cell = row.cells[c];
                rs = cell.rowSpan+r;
                cs = cell.colSpan+c;
                 
                for (i=r; i<rs; i++){
                    if (!a[i]){
                        a[i]=[];
                    }
                     
                    for (j=c; j<cs; j++){
                        a[i].push(i>r || j>c ? '' : "" + cell.innerHTML);
                    }
                }
            }
        }
 
        var ua = window.navigator.userAgent;
        var msie = ua.indexOf("MSIE "); 
 
        if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) // Se for Internet Explorer
        {
            for (r=0; r<a.length; r++){
                v = '';
                for (c=0; c<a[r].length; c++){
                    csv += (v + a[r][c]);
                    v = ",";
                }
                csv += '\r\n';
            }
             
            txtArea1.document.open("txt/html","replace");
            txtArea1.document.write(csv);
            txtArea1.document.close();
            txtArea1.focus(); 
            sa = txtArea1.document.execCommand("SaveAs",true,"CampanhaSMS.csv");   
 
        }  
        else
            for (r=0; r<a.length; r++){
                v = '';
                for (c=0; c<a[r].length; c++){
                    csv += (v + a[r][c]);
                    v = ",";
                }
                csv += "\n";
            }   
 
            var a         = document.createElement('a');
            a.href        = 'data:text/csv;charset=UTF-8,' + csv;
            a.target      = '_blank';
            a.download    = 'CampanhaSMS.csv';
            document.body.appendChild(a);
            a.click();
            //sa = window.open('data:attachment/csv,' + encodeURIComponent(csv));  
 
        //return (sa);
        return
    }
}
