import zipfile
import xml.etree.ElementTree as ET

def read_xlsx_strings(file_path):
    try:
        with zipfile.ZipFile(file_path, 'r') as archive:
            # Check if sharedStrings.xml exists
            if 'xl/sharedStrings.xml' in archive.namelist():
                xml_content = archive.read('xl/sharedStrings.xml')
                root = ET.fromstring(xml_content)
                # The namespace is usually something like {http://schemas.openxmlformats.org/spreadsheetml/2006/main}
                # But we can just search for 't' tags
                strings = []
                for elem in root.iter():
                    if elem.tag.endswith('t') and elem.text:
                        strings.append(elem.text)
                return strings
            else:
                return ["No shared strings found."]
    except Exception as e:
        return [f"Error: {e}"]

strings = read_xlsx_strings('c:/xampp/htdocs/angkringan-pos/Daftar_Pertanyaan_Wawancara_Master_Cafe_Bengkalis.xlsx')
for i, s in enumerate(strings):
    print(f"{i}: {s}")
