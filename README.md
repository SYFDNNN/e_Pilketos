#!/bin/bash

# ==============================================================================
# Skrip Setup Otomatis untuk Aplikasi e-Pilketos
#
# Skrip ini akan:
# 1. Memeriksa apakah Docker dan Docker Compose sudah terinstal.
# 2. Membuat file environment (.env) jika belum ada.
# 3. Membangun dan menjalankan semua layanan menggunakan Docker Compose.
# ==============================================================================

# Definisi warna untuk output yang lebih menarik
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Fungsi untuk menampilkan header
print_header() {
    echo -e "${GREEN}=========================================${NC}"
    echo -e "${GREEN}  Memulai Setup Aplikasi e-Pilketos 🚀  ${NC}"
    echo -e "${GREEN}=========================================${NC}\n"
}

# Fungsi untuk memeriksa error dan keluar
check_error() {
    if [ $? -ne 0 ]; then
        echo -e "\n${RED}====================================================${NC}"
        echo -e "${RED}  ❌ Terjadi kesalahan pada langkah sebelumnya.        ${NC}"
        echo -e "${RED}  Mohon periksa pesan error di atas. Skrip berhenti.  ${NC}"
        echo -e "${RED}====================================================${NC}"
        exit 1
    fi
}

# --- Mulai Skrip ---
print_header

# --- Langkah 1: Cek Prasyarat ---
echo -e "${YELLOW}Langkah 1: Memeriksa prasyarat (Docker & Docker Compose)...${NC}"
if ! command -v docker &> /dev/null; then
    echo -e "${RED}Error: Docker tidak ditemukan. Mohon install Docker terlebih dahulu sebelum menjalankan skrip ini.${NC}"
    exit 1
fi

if ! command -v docker-compose &> /dev/null; then
    echo -e "${RED}Error: Docker Compose tidak ditemukan. Pastikan Docker Desktop sudah terinstal dengan benar.${NC}"
    exit 1
fi
echo -e "${GREEN}✅ Prasyarat terpenuhi.${NC}\n"

# --- Langkah 2: Membuat file .env ---
echo -e "${YELLOW}Langkah 2: Menyiapkan file environment (.env)...${NC}"
if [ -f .env ]; then
    echo -e "${GREEN}✅ File .env sudah ada, langkah ini dilewati.${NC}\n"
else
    echo "Membuat file .env dengan kredensial default..."
    cat <<EOF > .env
# File environment untuk konfigurasi Docker
# Jangan gunakan password ini di lingkungan produksi!
MYSQL_ROOT_PASSWORD=admin123
EOF
    echo -e "${GREEN}✅ File .env berhasil dibuat.${NC}\n"
fi

# --- Langkah 3: Menjalankan Docker Compose ---
echo -e "${YELLOW}Langkah 3: Membangun dan menjalankan kontainer...${NC}"
echo "Proses ini mungkin memerlukan beberapa menit, terutama saat pertama kali dijalankan."
echo "Docker akan mengunduh image dan membangun lingkungan aplikasi..."

docker-compose up -d --build
check_error

# --- Langkah 4: Menampilkan Pesan Sukses ---
echo -e "\n${GREEN}======================================================${NC}"
echo -e "${GREEN}  🎉 Setup Selesai! Aplikasi e-Pilketos Siap Digunakan. 🎉 ${NC}"
echo -e "${GREEN}======================================================${NC}"
echo -e "\nAnda sekarang bisa mengakses layanan berikut:\n"
echo -e "  🌐 ${YELLOW}Aplikasi Utama${NC}   : http://localhost"
echo -e "  🗃️ ${YELLOW}phpMyAdmin${NC}      : http://localhost:8080\n"
echo -e "Kredensial login default untuk Admin adalah:\n"
echo -e "  👤 ${YELLOW}Username${NC} : admin"
echo -e "  🔑 ${YELLOW}Password${NC} : admin\n"
echo "Untuk pengguna siswa, silakan gunakan fitur registrasi pada halaman utama."
echo -e "\nUntuk menghentikan semua layanan, jalankan perintah: ${YELLOW}docker-compose down${NC}\n"
