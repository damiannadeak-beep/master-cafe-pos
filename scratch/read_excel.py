import pandas as pd
import json
import sys

try:
    df = pd.read_excel('c:/xampp/htdocs/angkringan-pos/Daftar_Pertanyaan_Wawancara_Master_Cafe_Bengkalis.xlsx')
    print(df.to_json(orient='records', force_ascii=False))
except Exception as e:
    print(f"Error: {e}")
