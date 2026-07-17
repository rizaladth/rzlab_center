package forms;

import java.awt.*;
import java.awt.event.*;
import java.sql.*;
import java.text.SimpleDateFormat;
import java.util.Date;
import javax.swing.*;
import javax.swing.table.*;
import javax.swing.border.*;
import koneksi.KoneksiDB;

public class FormTransaksi extends JPanel {

    private JTextField tfNoTransaksi, tfTanggal, tfNis, tfNamaSiswa, tfJumlahBayar;
    private JComboBox<String> cbJenisBayar;
    private JTable tableHistory;
    private DefaultTableModel modelHistory;
    private JButton btnProses, btnBatal, btnCetak, btnCariNis;

    public FormTransaksi() {
        initComponents();
        generateNoTransaksi();
        loadTodayHistory();
    }

    private void initComponents() {
        setLayout(new BorderLayout(10, 10));
        setBackground(Color.WHITE);
        setBorder(BorderFactory.createEmptyBorder(15, 15, 15, 15));

        // ── HEADER ──
        JLabel lblTitle = new JLabel("Transaksi Pembayaran");
        lblTitle.setFont(new Font("Segoe UI", Font.BOLD, 22));
        lblTitle.setForeground(new Color(41, 128, 185));
        add(lblTitle, BorderLayout.NORTH);

        // ── INPUT FORM ──
        JPanel inputPanel = new JPanel(new GridBagLayout());
        inputPanel.setBackground(new Color(245, 248, 250));
        inputPanel.setBorder(BorderFactory.createCompoundBorder(
                new LineBorder(new Color(41, 128, 185)),
                BorderFactory.createEmptyBorder(15, 15, 15, 15)));

        GridBagConstraints gbc = new GridBagConstraints();
        gbc.insets = new Insets(6, 8, 6, 8);
        gbc.fill = GridBagConstraints.HORIZONTAL;
        gbc.anchor = GridBagConstraints.WEST;

        // Row 0: No Trx + Tanggal
        addLbl(gbc, inputPanel, 0, 0, "No Transaksi:");
        tfNoTransaksi = new JTextField(16);
        tfNoTransaksi.setEditable(false);
        addFld(gbc, inputPanel, 1, 0, tfNoTransaksi);

        addLbl(gbc, inputPanel, 2, 0, "Tanggal:");
        tfTanggal = new JTextField(12);
        tfTanggal.setEditable(false);
        tfTanggal.setText(new SimpleDateFormat("yyyy-MM-dd").format(new Date()));
        addFld(gbc, inputPanel, 3, 0, tfTanggal);

        // Row 1: NIS + Cari + Nama
        addLbl(gbc, inputPanel, 0, 1, "NIS:");
        JPanel nisPanel = new JPanel(new FlowLayout(FlowLayout.LEFT, 3, 0));
        nisPanel.setBackground(new Color(245, 248, 250));
        tfNis = new JTextField(10);
        btnCariNis = new JButton("Cari");
        btnCariNis.setBackground(new Color(52, 152, 219));
        btnCariNis.setForeground(Color.WHITE);
        btnCariNis.setFocusPainted(false);
        btnCariNis.setFont(new Font("Segoe UI", Font.BOLD, 10));
        nisPanel.add(tfNis);
        nisPanel.add(btnCariNis);
        gbc.gridx = 1; gbc.gridy = 1; gbc.weightx = 1.0;
        inputPanel.add(nisPanel, gbc);

        addLbl(gbc, inputPanel, 2, 1, "Nama Siswa:");
        tfNamaSiswa = new JTextField(16);
        tfNamaSiswa.setEditable(false);
        addFld(gbc, inputPanel, 3, 1, tfNamaSiswa);

        // Row 2: Jenis Bayar + Jumlah
        addLbl(gbc, inputPanel, 0, 2, "Jenis Pembayaran:");
        cbJenisBayar = new JComboBox<>(new String[]{"SPP", "Kas Kelas", "Atribut", "Lain-lain"});
        cbJenisBayar.setPreferredSize(new Dimension(200, 28));
        addFld(gbc, inputPanel, 1, 2, cbJenisBayar);

        addLbl(gbc, inputPanel, 2, 2, "Jumlah Bayar (Rp):");
        tfJumlahBayar = new JTextField(16);
        addFld(gbc, inputPanel, 3, 2, tfJumlahBayar);

        // Row 3: Buttons
        JPanel btnPanel = new JPanel(new FlowLayout(FlowLayout.CENTER, 12, 5));
        btnPanel.setBackground(new Color(245, 248, 250));
        btnProses = createBtn("Proses Bayar", new Color(46, 204, 113));
        btnBatal = createBtn("Batal", new Color(231, 76, 60));
        btnCetak = createBtn("Cetak Struk", new Color(149, 165, 166));
        btnPanel.add(btnProses);
        btnPanel.add(btnBatal);
        btnPanel.add(btnCetak);
        gbc.gridx = 0; gbc.gridy = 3; gbc.gridwidth = 4;
        inputPanel.add(btnPanel, gbc);

        add(inputPanel, BorderLayout.NORTH);

        // ── HISTORY TABLE ──
        modelHistory = new DefaultTableModel(
                new Object[]{"No Transaksi", "Tanggal", "NIS", "Nama Siswa", "Jenis Bayar", "Jumlah"}, 0) {
            @Override
            public boolean isCellEditable(int row, int col) { return false; }
        };
        tableHistory = new JTable(modelHistory);
        tableHistory.setRowHeight(26);
        tableHistory.setFont(new Font("Segoe UI", Font.PLAIN, 12));
        tableHistory.getTableHeader().setFont(new Font("Segoe UI", Font.BOLD, 12));
        tableHistory.getTableHeader().setBackground(new Color(41, 128, 185));
        tableHistory.getTableHeader().setForeground(Color.WHITE);
        tableHistory.addMouseListener(new MouseAdapter() {
            @Override
            public void mouseClicked(MouseEvent e) { fillFromTable(); }
        });
        JScrollPane scroll = new JScrollPane(tableHistory);
        scroll.setBorder(BorderFactory.createTitledBorder(
                new LineBorder(new Color(41, 128, 185)),
                " Riwayat Transaksi Hari Ini ",
                TitledBorder.LEFT, TitledBorder.TOP,
                new Font("Segoe UI", Font.BOLD, 12), new Color(41, 128, 185)));
        add(scroll, BorderLayout.CENTER);

        // ── ACTIONS ──
        btnCariNis.addActionListener(e -> cariSiswa());
        tfNis.addKeyListener(new KeyAdapter() {
            @Override
            public void keyReleased(KeyEvent e) {
                if (e.getKeyCode() == KeyEvent.VK_ENTER) cariSiswa();
            }
        });
        btnProses.addActionListener(e -> prosesBayar());
        btnBatal.addActionListener(e -> resetForm());
        btnCetak.addActionListener(e -> cetakStruk());
    }

    private void addLbl(GridBagConstraints gbc, JPanel p, int col, int row, String text) {
        gbc.gridx = col; gbc.gridy = row; gbc.weightx = 0; gbc.gridwidth = 1;
        JLabel l = new JLabel(text);
        l.setFont(new Font("Segoe UI", Font.PLAIN, 12));
        p.add(l, gbc);
    }

    private void addFld(GridBagConstraints gbc, JPanel p, int col, int row, JComponent f) {
        gbc.gridx = col; gbc.gridy = row; gbc.weightx = 1.0; gbc.gridwidth = 1;
        p.add(f, gbc);
    }

    private JButton createBtn(String text, Color bg) {
        JButton b = new JButton(text);
        b.setBackground(bg);
        b.setForeground(Color.WHITE);
        b.setFocusPainted(false);
        b.setFont(new Font("Segoe UI", Font.BOLD, 11));
        b.setBorder(BorderFactory.createEmptyBorder(8, 18, 8, 18));
        b.setCursor(Cursor.getPredefinedCursor(Cursor.HAND_CURSOR));
        return b;
    }

    // ─── DATABASE OPERATIONS ───

    private void generateNoTransaksi() {
        String today = new SimpleDateFormat("yyyyMMdd").format(new Date());
        String prefix = "TRX-" + today + "-";
        try {
            Connection conn = KoneksiDB.getKoneksi();
            PreparedStatement ps = conn.prepareStatement(
                    "SELECT no_transaksi FROM transaksi WHERE no_transaksi LIKE ? ORDER BY no_transaksi DESC LIMIT 1");
            ps.setString(1, prefix + "%");
            ResultSet rs = ps.executeQuery();
            String newNo;
            if (rs.next()) {
                String last = rs.getString("no_transaksi");
                try {
                    String seqStr = last.substring(last.lastIndexOf('-') + 1);
                    int seq = Integer.parseInt(seqStr) + 1;
                    newNo = String.format("%s%03d", prefix, seq);
                } catch (NumberFormatException e) {
                    newNo = prefix + "001";
                }
            } else {
                newNo = prefix + "001";
            }
            tfNoTransaksi.setText(newNo);
            rs.close();
            ps.close();
        } catch (Exception e) {
            tfNoTransaksi.setText(prefix + String.format("%03d", (int)(Math.random() * 900) + 100));
        }
    }

    private void cariSiswa() {
        String nis = tfNis.getText().trim();
        if (nis.isEmpty()) {
            JOptionPane.showMessageDialog(this, "Masukkan NIS!");
            return;
        }
        try {
            Connection conn = KoneksiDB.getKoneksi();
            PreparedStatement ps = conn.prepareStatement(
                    "SELECT nama_siswa FROM siswa WHERE nis = ?");
            ps.setString(1, nis);
            ResultSet rs = ps.executeQuery();
            if (rs.next()) {
                tfNamaSiswa.setText(rs.getString("nama_siswa"));
            } else {
                tfNamaSiswa.setText("");
                JOptionPane.showMessageDialog(this, "NIS " + nis + " tidak ditemukan!");
            }
            rs.close(); ps.close();
        } catch (SQLException e) {
            JOptionPane.showMessageDialog(this, "Gagal cari: " + e.getMessage());
        }
    }

    private void fillFromTable() {
        int row = tableHistory.getSelectedRow();
        if (row < 0) return;
        tfNoTransaksi.setText(modelHistory.getValueAt(row, 0).toString());
        tfTanggal.setText(modelHistory.getValueAt(row, 1).toString());
        tfNis.setText(modelHistory.getValueAt(row, 2).toString());
        tfNamaSiswa.setText(modelHistory.getValueAt(row, 3).toString());
        cbJenisBayar.setSelectedItem(modelHistory.getValueAt(row, 4).toString());
        String amt = modelHistory.getValueAt(row, 5).toString()
                .replace("Rp ", "").replace(",", "").trim();
        tfJumlahBayar.setText(amt);
    }

    private void prosesBayar() {
        String nis = tfNis.getText().trim();
        String nama = tfNamaSiswa.getText().trim();
        String jenis = cbJenisBayar.getSelectedItem().toString();
        String jmlStr = tfJumlahBayar.getText().trim();

        if (nis.isEmpty() || nama.isEmpty()) {
            JOptionPane.showMessageDialog(this, "Cari NIS siswa terlebih dahulu!");
            return;
        }
        if (jmlStr.isEmpty()) {
            JOptionPane.showMessageDialog(this, "Jumlah bayar harus diisi!");
            tfJumlahBayar.requestFocus(); return;
        }

        double jumlah;
        try {
            jumlah = Double.parseDouble(jmlStr.replace(",", "").replace(".", ""));
        } catch (NumberFormatException e) {
            JOptionPane.showMessageDialog(this, "Format jumlah tidak valid!");
            return;
        }
        if (jumlah <= 0) {
            JOptionPane.showMessageDialog(this, "Jumlah harus lebih dari 0!");
            return;
        }

        int c = JOptionPane.showConfirmDialog(this,
                String.format("Konfirmasi pembayaran:\nNIS: %s\nNama: %s\n%s: Rp %,.0f",
                        nis, nama, jenis, jumlah),
                "Konfirmasi", JOptionPane.YES_NO_OPTION);
        if (c != JOptionPane.YES_OPTION) return;

        try {
            Connection conn = KoneksiDB.getKoneksi();
            PreparedStatement ps = conn.prepareStatement(
                    "INSERT INTO transaksi (no_transaksi, tanggal, nis, nama_siswa, "
                    + "jenis_pembayaran, jumlah_bayar) VALUES (?, ?, ?, ?, ?, ?)");
            ps.setString(1, tfNoTransaksi.getText());
            ps.setString(2, tfTanggal.getText());
            ps.setString(3, nis);
            ps.setString(4, nama);
            ps.setString(5, jenis);
            ps.setDouble(6, jumlah);
            if (ps.executeUpdate() > 0) {
                JOptionPane.showMessageDialog(this, "Pembayaran berhasil!");
                resetForm();
                loadTodayHistory();
                generateNoTransaksi();
            }
            ps.close();
        } catch (SQLException e) {
            JOptionPane.showMessageDialog(this, "Gagal proses: " + e.getMessage());
        }
    }

    private void resetForm() {
        tfNis.setText("");
        tfNamaSiswa.setText("");
        tfJumlahBayar.setText("");
        cbJenisBayar.setSelectedIndex(0);
        tableHistory.clearSelection();
    }

    private void cetakStruk() {
        if (tfNamaSiswa.getText().isEmpty()) {
            JOptionPane.showMessageDialog(this, "Pilih atau proses transaksi terlebih dahulu!");
            return;
        }
        StringBuilder struk = new StringBuilder();
        struk.append("======== STRUK PEMBAYARAN ========\n");
        struk.append(String.format("No Trx   : %s\n", tfNoTransaksi.getText()));
        struk.append(String.format("Tanggal  : %s\n", tfTanggal.getText()));
        struk.append("----------------------------------\n");
        struk.append(String.format("NIS      : %s\n", tfNis.getText()));
        struk.append(String.format("Nama     : %s\n", tfNamaSiswa.getText()));
        struk.append(String.format("Jenis    : %s\n", cbJenisBayar.getSelectedItem()));
        struk.append("----------------------------------\n");
        struk.append(String.format("Jumlah   : Rp %s\n", tfJumlahBayar.getText()));
        struk.append("==================================\n");
        struk.append("Terima kasih.\n");

        JTextArea ta = new JTextArea(struk.toString());
        ta.setFont(new Font("Monospaced", Font.PLAIN, 13));
        ta.setEditable(false);
        JOptionPane.showMessageDialog(this, new JScrollPane(ta),
                "Preview Struk", JOptionPane.INFORMATION_MESSAGE);
    }

    private void loadTodayHistory() {
        modelHistory.setRowCount(0);
        try {
            Connection conn = KoneksiDB.getKoneksi();
            PreparedStatement ps = conn.prepareStatement(
                    "SELECT no_transaksi, tanggal, nis, nama_siswa, jenis_pembayaran, jumlah_bayar "
                    + "FROM transaksi WHERE tanggal = CURDATE() ORDER BY no_transaksi DESC");
            ResultSet rs = ps.executeQuery();
            while (rs.next()) {
                modelHistory.addRow(new Object[]{
                        rs.getString("no_transaksi"),
                        rs.getString("tanggal"),
                        rs.getString("nis"),
                        rs.getString("nama_siswa"),
                        rs.getString("jenis_pembayaran"),
                        String.format("Rp %,.0f", rs.getDouble("jumlah_bayar"))
                });
            }
            rs.close(); ps.close();
        } catch (SQLException e) {
            JOptionPane.showMessageDialog(this, "Gagal load riwayat: " + e.getMessage());
        }
    }
}
