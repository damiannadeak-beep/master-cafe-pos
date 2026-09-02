import pandas as pd
import os

questions = [
    {
        "No": 1,
        "Topik": "Kendala Operasional Harian",
        "Pertanyaan": "Apa kendala terbesar yang paling sering mengganggu kelancaran pesanan dari meja pelanggan hingga ke dapur?"
    },
    {
        "No": 2,
        "Topik": "Keuangan & Kasir (Shift)",
        "Pertanyaan": "Apakah pernah atau sering terjadi selisih antara total catatan penjualan dengan uang fisik yang ada di laci kasir saat tutup warung?"
    },
    {
        "No": 3,
        "Topik": "Pemantauan Stok Bahan Baku",
        "Pertanyaan": "Bagaimana cara Bapak/Ibu mengecek sisa bahan baku? Apakah karyawan pernah lupa mencatat sehingga bahan tiba-tiba habis saat jam sibuk?"
    },
    {
        "No": 4,
        "Topik": "Efisiensi Pembayaran",
        "Pertanyaan": "Bagaimana cara kasir mencatat dan memverifikasi pembayaran digital (seperti QRIS atau transfer)? Apakah prosesnya sudah cepat atau masih memakan waktu?"
    },
    {
        "No": 5,
        "Topik": "Solusi Pemesanan Mandiri (Self-Order)",
        "Pertanyaan": "Jika ada sistem di mana pelanggan bisa memesan dan langsung membayar sendiri dari meja (via Scan QR), apakah menurut Bapak/Ibu itu akan sangat membantu?"
    },
    {
        "No": 6,
        "Topik": "Harapan Terhadap Sistem Baru",
        "Pertanyaan": "Fitur atau jenis laporan seperti apa yang paling Bapak/Ibu butuhkan agar pengelolaan Master Cafe menjadi jauh lebih mudah ke depannya?"
    }
]

# Write to a new Excel file to avoid PermissionError if the old one is open
excel_path = r'c:\xampp\htdocs\angkringan-pos\docs\Pertanyaan_Wawancara_Master_Cafe.xlsx'
df = pd.DataFrame(questions)
df.to_excel(excel_path, index=False)
print(f"Created new Excel Document at {excel_path}")
