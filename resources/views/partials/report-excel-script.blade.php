<script>
(() => {
    // Inline string cells preserve user content as text, never as formulas.
    const xml = value => String(value).replace(/[\x00-\x08\x0B\x0C\x0E-\x1F]/g, '')
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    const column = index => { let name = ''; for (index++; index; index = Math.floor((index - 1) / 26)) name = String.fromCharCode(65 + (index - 1) % 26) + name; return name; };
    function workbook(sheets) {
        const styles = '<?xml version="1.0"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><color rgb="FFFFFFFF"/><sz val="11"/><name val="Calibri"/></font></fonts><fills count="6"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF123641"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFFFE4E6"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFFFF3BF"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFDCFCE7"/></patternFill></fill></fills><borders count="2"><border/><border><left style="thin"><color rgb="FFDCE5EB"/></left><right style="thin"><color rgb="FFDCE5EB"/></right><top style="thin"><color rgb="FFDCE5EB"/></top><bottom style="thin"><color rgb="FFDCE5EB"/></bottom></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="5">' + [0,1,2,3,4].map(i => '<xf numFmtId="0" fontId="'+(i===1?1:0)+'" fillId="'+(i===0?0:i+1)+'" borderId="1" xfId="0" applyAlignment="1" applyFill="1" applyFont="1" applyBorder="1"><alignment vertical="top" wrapText="1"/></xf>').join('') + '</cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>';
        const files = {
            '_rels/.rels': '<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>',
            'xl/styles.xml': styles,
            'xl/workbook.xml': '<?xml version="1.0"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets>'+sheets.map((sheet,i)=>'<sheet name="'+xml(sheet.name)+'" sheetId="'+(i+1)+'" r:id="rId'+(i+1)+'"/>').join('')+'</sheets></workbook>',
            'xl/_rels/workbook.xml.rels': '<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'+sheets.map((_,i)=>'<Relationship Id="rId'+(i+1)+'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet'+(i+1)+'.xml"/>').join('')+'<Relationship Id="styles" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>'
        };
        const overrides = ['<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>','<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'];
        sheets.forEach((sheet,index) => {
            const id=index+1;
            const rows=sheet.rows;
            const count=Math.max(1,...rows.map(row=>row.values.length));
            const cols=Array.from({length:count},(_,j)=>'<col min="'+(j+1)+'" max="'+(j+1)+'" width="'+(j===0?42:28)+'" customWidth="1"/>').join('');
            files['xl/worksheets/sheet'+id+'.xml']='<?xml version="1.0"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews><cols>'+cols+'</cols><sheetData>'+rows.map((row,i)=>'<row r="'+(i+1)+'">'+row.values.map((value,j)=>{
                const style=row.header?1:({high:2,medium:3,low:4}[row.tones?.[j]]||0);
                const ref=column(j)+(i+1);
                return typeof value==='number' && Number.isFinite(value)?'<c r="'+ref+'" s="'+style+'"><v>'+value+'</v></c>':'<c r="'+ref+'" s="'+style+'" t="inlineStr"><is><t xml:space="preserve">'+xml(value)+'</t></is></c>';
            }).join('')+'</row>').join('')+'</sheetData>'+(sheet.table?'<tableParts count="1"><tablePart r:id="table"/></tableParts>':'')+'</worksheet>';
            overrides.push('<Override PartName="/xl/worksheets/sheet'+id+'.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>');
            if(sheet.table){
                const ref='A1:'+column(count-1)+rows.length;
                files['xl/tables/table'+id+'.xml']='<?xml version="1.0"?><table xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" id="'+id+'" name="RiskComparison" displayName="RiskComparison" ref="'+ref+'" totalsRowShown="0"><autoFilter ref="'+ref+'"/><tableColumns count="'+count+'">'+rows[0].values.map((v,j)=>'<tableColumn id="'+(j+1)+'" name="'+xml(v)+'"/>').join('')+'</tableColumns><tableStyleInfo name="TableStyleMedium2" showFirstColumn="0" showLastColumn="0" showRowStripes="1" showColumnStripes="0"/></table>';
                files['xl/worksheets/_rels/sheet'+id+'.xml.rels']='<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="table" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/table" Target="../tables/table'+id+'.xml"/></Relationships>';
                overrides.push('<Override PartName="/xl/tables/table'+id+'.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.table+xml"/>');
            }
        });
        files['[Content_Types].xml']='<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/>'+overrides.join('')+'</Types>';
        const encoder = new TextEncoder(), local = [], central = [];
        let offset = 0, centralSize = 0;
        const crc32 = bytes => { let crc = 0xffffffff; for (const byte of bytes) { crc ^= byte; for (let i = 0; i < 8; i++) crc = (crc >>> 1) ^ (0xedb88320 & -(crc & 1)); } return (crc ^ 0xffffffff) >>> 0; };
        for (const [path, content] of Object.entries(files)) {
            const name = encoder.encode(path), data = encoder.encode(content), crc = crc32(data);
            const header = new Uint8Array(30 + name.length), h = new DataView(header.buffer);
            h.setUint32(0, 0x04034b50, true); h.setUint16(4, 20, true); h.setUint16(12, 33, true);
            h.setUint32(14, crc, true); h.setUint32(18, data.length, true); h.setUint32(22, data.length, true); h.setUint16(26, name.length, true); header.set(name, 30);
            const directory = new Uint8Array(46 + name.length), d = new DataView(directory.buffer);
            d.setUint32(0, 0x02014b50, true); d.setUint16(4, 20, true); d.setUint16(6, 20, true); d.setUint16(14, 33, true);
            d.setUint32(16, crc, true); d.setUint32(20, data.length, true); d.setUint32(24, data.length, true); d.setUint16(28, name.length, true); d.setUint32(42, offset, true); directory.set(name, 46);
            local.push(header, data); central.push(directory); offset += header.length + data.length; centralSize += directory.length;
        }
        const end = new Uint8Array(22), e = new DataView(end.buffer);
        e.setUint32(0, 0x06054b50, true); e.setUint16(8, central.length, true); e.setUint16(10, central.length, true); e.setUint32(12, centralSize, true); e.setUint32(16, offset, true);
        return new Blob([...local, ...central, end], {type:'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'});
    }
    const tone = cell => ['high','medium','low'].find(value => cell.querySelector('.risk-'+value)) || null;
    const tableRows = table => Array.from(table.rows, row => ({
        values: Array.from(row.cells, cell => { const text=cell.innerText.trim(); return cell.tagName==='TD' && /^-?\d+(\.\d+)?$/.test(text)?Number(text):text; }),
        header: row.parentElement.tagName==='THEAD',
        tones: Array.from(row.cells, tone)
    }));
    document.getElementById('export-excel').addEventListener('click', event => {
        const rows = [{values:['Assessment Report #' + event.currentTarget.dataset.assessmentId],header:true}];
        document.querySelectorAll('#main-content > section, #main-content > .report-detail-grid > section').forEach(section => {
            section.querySelectorAll('h2,h3,h4,p,table,dl').forEach(element => {
                if (element.closest('table') && element.tagName !== 'TABLE') return;
                if (element.tagName === 'TABLE') rows.push(...tableRows(element));
                else if (element.tagName === 'DL') element.querySelectorAll('dt').forEach(dt => rows.push({values:[dt.innerText.trim(), dt.nextElementSibling?.innerText.trim() || '']}));
                else { const text=element.innerText.trim(); if(text) rows.push({values:[text],header:/^H[234]$/.test(element.tagName)}); }
            });
            rows.push({values:[]});
        });
        const comparison=document.getElementById('risk-comparison-heading').closest('section').querySelector('table');
        const sheets=[{name:'Risk Comparison',rows:tableRows(comparison),table:true},{name:'Assessment Report',rows}];
        const url=URL.createObjectURL(workbook(sheets)), link=document.createElement('a');
        link.href=url; link.download='assessment-'+event.currentTarget.dataset.assessmentId+'.xlsx';
        document.body.appendChild(link); link.click(); link.remove(); setTimeout(()=>URL.revokeObjectURL(url),1000);
    });
})();
</script>
