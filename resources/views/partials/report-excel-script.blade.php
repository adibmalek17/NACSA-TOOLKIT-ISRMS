<script>
(() => {
    // Inline string cells preserve user content as text, never as formulas.
    const xml = value => String(value).replace(/[\x00-\x08\x0B\x0C\x0E-\x1F]/g, '')
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    const column = index => { let name = ''; for (index++; index; index = Math.floor((index - 1) / 26)) name = String.fromCharCode(65 + (index - 1) % 26) + name; return name; };
    function workbook(rows) {
        const sheet = '<?xml version="1.0" encoding="UTF-8"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>' + rows.map((row, i) => '<row r="' + (i + 1) + '">' + row.map((value, j) => '<c r="' + column(j) + (i + 1) + '" t="inlineStr"><is><t xml:space="preserve">' + xml(value) + '</t></is></c>').join('') + '</row>').join('') + '</sheetData></worksheet>';
        const files = {
            '[Content_Types].xml': '<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>',
            '_rels/.rels': '<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>',
            'xl/workbook.xml': '<?xml version="1.0"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Assessment report" sheetId="1" r:id="rId1"/></sheets></workbook>',
            'xl/_rels/workbook.xml.rels': '<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>',
            'xl/worksheets/sheet1.xml': sheet
        };
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
    document.getElementById('export-excel').addEventListener('click', event => {
        const rows = [['Assessment report #' + event.currentTarget.dataset.assessmentId]];
        document.querySelectorAll('#main-content section').forEach(section => {
            section.querySelectorAll('h2,h3,h4,p,tr,dt,dd').forEach(element => {
                if (element.closest('table') && element.tagName !== 'TR') return;
                if (element.tagName === 'TR') rows.push(Array.from(element.cells, cell => cell.innerText.trim()));
                else { const text = element.innerText.trim(); if (text) rows.push([text]); }
            });
            rows.push([]);
        });
        const url = URL.createObjectURL(workbook(rows)), link = document.createElement('a');
        link.href = url; link.download = 'assessment-' + event.currentTarget.dataset.assessmentId + '.xlsx';
        document.body.appendChild(link); link.click(); link.remove(); setTimeout(() => URL.revokeObjectURL(url), 1000);
    });
})();
</script>
