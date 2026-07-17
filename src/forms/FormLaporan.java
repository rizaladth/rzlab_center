package forms;

import java.awt.*;
import java.awt.event.*;
import java.io.*;
import java.sql.*;
import java.text.SimpleDateFormat;
import java.util.Date;
import javax.swing.*;
import javax.swing.table.*;
import javax.swing.border.*;
import koneksi.KoneksiDB;

public class FormLaporan extends JPanel {

    private JTable table;
    private DefaultTableModel model;
    private JComboBox<String> cbJenisLaporan, cbFilterKelas;
    private JTextField tfTglMulai, tfTglSelesai;
    private JButton btnPreview, btnExport;
    private JLabel lblSummary;

    public FormLaporan() {
        initComponents();
    }

    private void initComponents() {
        setLayout(new BorderLayout(10, 10));
        setBackground(Color.WHITE);
        setBorder(BorderFactory.createEmptyBorder(15, 15, 15, 15));

        // ── HEADER ──
        JLabel lblTitle = new JLabel("Laporan");
        lblTitle.setFont(new Font("Segoe UI", Font.BOLD, 22));
        lblTitle.setForeground(new Color(41, 128, 185));
        add(lblTitle, BorderLayout.NORTH);

        // ── FILTER PANEL ──
        JPanel filterPanel = new JPanel(new FlowLayout(FlowLayout.LEFT, 10, 8));
        filterPanel.setBackground(new Color(245, 248, 250));
        filterPanel.setBorder(BorderFactory.createCompoundBorder(
                new LineBorder(new Color(41, 128, 185)),
                BorderFactory.createEmptyBorder(5, 10, 5, 10)));

        filterPanel.add(makeLabel("Jenis Laporan:"));
        cbJenisLaporan = new JComboBox<>(new String[]{
                "Laporan Absensi Harian",
                "Laporan Absensi Per Kelas",
                "Laporan Transaksi Kas/SPP"
        });
        cbJenisLaporan.setPreferredSize(new Dimension(220, 28));
        filterPanel.add(cbJenisLaporan);

        filterPanel.add(Box.createHorizontalStrut(15));
        filterPanel.add(makeLabel("Dari (yyyy-MM-dd):"));
        tfTglMulai = new JTextField(10);
        tfTglMulai.setText(new SimpleDateFormat("yyyy-MM-dd").format(new Date()));
        filterPanel.add(tfTglMulai);

        filterPanel.add(makeLabel(" Sampai:"));
        tfTglSelesai = new JTextField(10);
        tfTglSelesai.setText(new SimpleDateFormat("yyyy-MM-dd").format(new Date()));
        filterPanel.add(tfTglSelesai);

        filterPanel.add(Box.createHorizontalStrut(15));
        filterPanel.add(makeLabel("Kelas:"));
        cbFilterKelas = new JComboBox<>();
        cbFilterKelas.addItem("Semua");
        cbFilterKelas.setPreferredSize(new Dimension(160, 28));
        loadKelasFilter();
        filterPanel.add(cbFilterKelas);

        filterPanel.add(Box.createHorizontalStrut(15));
        btnPreview = createBtn("Preview", new Color(41, 128, 185));
        btnExport = createBtn("Export CSV", new Color(46, 204, 113));
        filterPanel.add(btnPreview);
        filterPanel.add(btnExport);

        add(filterPanel, BorderLayout.PAGE_START);

        // ── TABLE ──
        model = new DefaultTableModel() {
            @Override
            public boolean isCellEditable(int row, int col) { return false; }
        };
        table = new JTable(model);
        table.setRowHeight(26);
        table.setFont(new Font("Segoe UI", Font.PLAIN, 12));
        table.getTableHeader().setFont(new Font("Segoe UI", Font.BOLD, 12));
        table.getTableHeader().setBackground(new Color(41, 128, 185));
        table.getTableHeader().setForeground(Color.WHITE);
        JScrollPane scroll = new JScrollPane(table);
        scroll.setBorder(new LineBorder(new Color(200, 200, 200)));
        add(scroll, BorderLayout.CENTER);

        // ── SUMMARY BAR ──
        JPanel summaryPanel = new JPanel(new FlowLayout(FlowLayout.LEFT));
        summaryPanel.setBackground(new Color(245, 248, 250));
        summaryPanel.setBorder(BorderFactory.createCompoundBorder(
                new LineBorder(new Color(220, 220, 220)),
                BorderFactory.createEmptyBorder(6, 10, 6, 10)));
        lblSummary = new JLabel("Total data: 0");
        lblSummary.setFont(new Font("Segoe UI", Font.BOLD, 12));
        lblSummary.setForeground(new Color(41, 128, 185));
        summaryPanel.add(lblSummary);
        add(summaryPanel, BorderLayout.SOUTH);

        btnPreview.addActionListener(e -> previewData());
        btnExport.addActionListener(e -> exportCSV());
    }

    private JLabel makeLabel(String text) {
        JLabel l = new JLabel(text);
        l.setFont(new Font("Segoe UI", Font.PLAIN, 12));
        return l;
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

    private void loadKelasFilter() {
        try {
            Connection conn = KoneksiDB.getKoneksi();
            PreparedStatement ps = conn.prepareStatement(
                    "SELECT id_kelas, nama_kelas FROM kelas ORDER BY nama_kelas");
            ResultSet rs = ps.executeQuery();
            while (rs.next()) {
                cbFilterKelas.addItem(rs.getString("id_kelas") + " - " + rs.getString("nama_kelas"));
            }
            rs.close();
            ps.close();
        } catch (SQLException e) {
            JOptionPane.showMessageDialog(this, "Gagal load kelas: " + e.getMessage());
        }
    }

    private String getKelasFilterId() {
        String val = cbFilterKelas.getSelectedItem().toString();
        if (val.equals("Semua")) return null;
        return val.split(" - ")[0].trim();
    }

    // ─── REPORT QUERIES ───

    private void previewData() {
        String jenis = cbJenisLaporan.getSelectedItem().toString();
        String tglMulai = tfTglMulai.getText().trim();
        String tglSelesai = tfTglSelesai.getText().trim();
        String kelasId = getKelasFilterId();

        if (!isValidDate(tglMulai) || !isValidDate(tglSelesai)) {
            JOptionPane.showMessageDialog(this, "Format tanggal harus yyyy-MM-dd!");
            return;
        }

        model.setRowCount(0);

        try {
            Connection conn = KoneksiDB.getKoneksi();
            PreparedStatement ps;
            ResultSet rs;

            switch (jenis) {
                case "Laporan Absensi Harian":
                    model.setColumnIdentifiers(new Object[]{
                            "Tanggal", "NIS", "Nama Siswa", "Kelas", "Status", "Keterangan"
                    });
                    String sqlHarian = "SELECT a.tanggal, a.nis, s.nama_siswa, "
                            + "CONCAT(s.id_kelas,' - ',k.nama_kelas) AS kelas, "
                            + "a.status, a.keterangan "
                            + "FROM absensi a JOIN siswa s ON a.nis = s.nis "
                            + "LEFT JOIN kelas k ON s.id_kelas = k.id_kelas "
                            + "WHERE a.tanggal BETWEEN ? AND ?";
                    if (kelasId != null) sqlHarian += " AND s.id_kelas = ?";
                    sqlHarian += " ORDER BY a.tanggal, s.nama_siswa";
                    ps = conn.prepareStatement(sqlHarian);
                    ps.setString(1, tglMulai);
                    ps.setString(2, tglSelesai);
                    if (kelasId != null) ps.setString(3, kelasId);
                    rs = ps.executeQuery();
                    while (rs.next()) {
                        model.addRow(new Object[]{
                                rs.getString("tanggal"), rs.getString("nis"),
                                rs.getString("nama_siswa"), rs.getString("kelas"),
                                rs.getString("status"), rs.getString("keterangan")
                        });
                    }
                    rs.close(); ps.close();
                    break;

                case "Laporan Absensi Per Kelas":
                    model.setColumnIdentifiers(new Object[]{
                            "Kelas", "Tanggal", "Hadir", "Izin", "Sakit", "Alpha"
                    });
                    String sqlPerKelas = "SELECT CONCAT(s.id_kelas,' - ',k.nama_kelas) AS kelas, "
                            + "a.tanggal, "
                            + "SUM(CASE WHEN a.status='Hadir' THEN 1 ELSE 0 END) AS hadir, "
                            + "SUM(CASE WHEN a.status='Izin' THEN 1 ELSE 0 END) AS izin, "
                            + "SUM(CASE WHEN a.status='Sakit' THEN 1 ELSE 0 END) AS sakit, "
                            + "SUM(CASE WHEN a.status='Alpha' THEN 1 ELSE 0 END) AS alpha "
                            + "FROM absensi a JOIN siswa s ON a.nis = s.nis "
                            + "LEFT JOIN kelas k ON s.id_kelas = k.id_kelas "
                            + "WHERE a.tanggal BETWEEN ? AND ?";
                    if (kelasId != null) sqlPerKelas += " AND s.id_kelas = ?";
                    sqlPerKelas += " GROUP BY kelas, a.tanggal ORDER BY kelas, a.tanggal";
                    ps = conn.prepareStatement(sqlPerKelas);
                    ps.setString(1, tglMulai);
                    ps.setString(2, tglSelesai);
                    if (kelasId != null) ps.setString(3, kelasId);
                    rs = ps.executeQuery();
                    while (rs.next()) {
                        model.addRow(new Object[]{
                                rs.getString("kelas"), rs.getString("tanggal"),
                                rs.getInt("hadir"), rs.getInt("izin"),
                                rs.getInt("sakit"), rs.getInt("alpha")
                        });
                    }
                    rs.close(); ps.close();
                    break;

                case "Laporan Transaksi Kas/SPP":
                    model.setColumnIdentifiers(new Object[]{
                            "No Transaksi", "Tanggal", "NIS", "Nama Siswa", "Jenis Bayar", "Jumlah Bayar"
                    });
                    String sqlTrx = "SELECT t.no_transaksi, t.tanggal, t.nis, t.nama_siswa, "
                            + "t.jenis_pembayaran, t.jumlah_bayar "
                            + "FROM transaksi t WHERE t.tanggal BETWEEN ? AND ?";
                    if (kelasId != null) sqlTrx += " AND t.nis IN (SELECT nis FROM siswa WHERE id_kelas=?)";
                    sqlTrx += " ORDER BY t.tanggal DESC";
                    ps = conn.prepareStatement(sqlTrx);
                    ps.setString(1, tglMulai);
                    ps.setString(2, tglSelesai);
                    if (kelasId != null) ps.setString(3, kelasId);
                    rs = ps.executeQuery();
                    while (rs.next()) {
                        model.addRow(new Object[]{
                                rs.getString("no_transaksi"), rs.getString("tanggal"),
                                rs.getString("nis"), rs.getString("nama_siswa"),
                                rs.getString("jenis_pembayaran"),
                                String.format("Rp %,.0f", rs.getDouble("jumlah_bayar"))
                        });
                    }
                    rs.close(); ps.close();
                    break;
            }

            if (model.getRowCount() == 0) {
                JOptionPane.showMessageDialog(this, "Tidak ada data untuk filter ini.");
            }
            updateSummary(jenis);
        } catch (SQLException e) {
            JOptionPane.showMessageDialog(this, "Gagal load laporan: " + e.getMessage());
        }
    }

    private void exportCSV() {
        if (model.getRowCount() == 0) {
            JOptionPane.showMessageDialog(this, "Tidak ada data untuk di-export!");
            return;
        }
        JFileChooser fc = new JFileChooser();
        fc.setSelectedFile(new File("laporan_" + new SimpleDateFormat("yyyyMMdd_HHmmss").format(new Date()) + ".csv"));
        if (fc.showSaveDialog(this) == JFileChooser.APPROVE_OPTION) {
            try (FileWriter fw = new FileWriter(fc.getSelectedFile())) {
                for (int c = 0; c < model.getColumnCount(); c++) {
                    fw.write(model.getColumnName(c));
                    if (c < model.getColumnCount() - 1) fw.write(",");
                }
                fw.write("\n");
                for (int r = 0; r < model.getRowCount(); r++) {
                    for (int c = 0; c < model.getColumnCount(); c++) {
                        Object v = model.getValueAt(r, c);
                        fw.write(v != null ? v.toString() : "");
                        if (c < model.getColumnCount() - 1) fw.write(",");
                    }
                    fw.write("\n");
                }
                fw.flush();
                JOptionPane.showMessageDialog(this,
                        "Berhasil di-export:\n" + fc.getSelectedFile().getAbsolutePath());
            } catch (IOException e) {
                JOptionPane.showMessageDialog(this, "Gagal export: " + e.getMessage());
            }
        }
    }

    private boolean isValidDate(String s) {
        try {
            SimpleDateFormat f = new SimpleDateFormat("yyyy-MM-dd");
            f.setLenient(false);
            f.parse(s);
            return true;
        } catch (Exception e) { return false; }
    }

    private void updateSummary(String jenis) {
        int total = model.getRowCount();
        String text = "Total data: " + total;
        if (jenis.equals("Laporan Transaksi Kas/SPP") && total > 0) {
            double totalBayar = 0;
            int colIdx = model.getColumnCount() - 1;
            for (int r = 0; r < total; r++) {
                try {
                    String val = model.getValueAt(r, colIdx).toString()
                            .replace("Rp ", "").replace(",", "").trim();
                    totalBayar += Double.parseDouble(val);
                } catch (Exception ignored) {}
            }
            text += String.format("  |  Total Pembayaran: Rp %,.0f", totalBayar);
        }
        lblSummary.setText(text);
    }
}
