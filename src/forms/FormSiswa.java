package forms;

import java.awt.*;
import java.awt.event.*;
import java.sql.*;
import javax.swing.*;
import javax.swing.table.*;
import javax.swing.border.*;
import koneksi.KoneksiDB;

public class FormSiswa extends JPanel {

    private JTable table;
    private DefaultTableModel model;
    private JTextField tfNis, tfNama, tfAlamat, tfTelp, tfCari;
    private JComboBox<String> cbKelamin, cbKelas;
    private JButton btnTambah, btnUbah, btnHapus, btnBersih;
    private String selectedNis = null;

    public FormSiswa() {
        initComponents();
        loadKelasCombo();
        loadData();
    }

    private void initComponents() {
        setLayout(new BorderLayout(10, 10));
        setBackground(Color.WHITE);
        setBorder(BorderFactory.createEmptyBorder(15, 15, 15, 15));

        // ── HEADER ──
        JPanel headerPanel = new JPanel(new BorderLayout());
        headerPanel.setBackground(Color.WHITE);
        JLabel lblTitle = new JLabel("Manajemen Data Siswa");
        lblTitle.setFont(new Font("Segoe UI", Font.BOLD, 22));
        lblTitle.setForeground(new Color(41, 128, 185));
        headerPanel.add(lblTitle, BorderLayout.WEST);
        add(headerPanel, BorderLayout.NORTH);

        // ── CENTER: Search + Table ──
        JPanel centerPanel = new JPanel(new BorderLayout(5, 5));
        centerPanel.setBackground(Color.WHITE);

        JPanel searchPanel = new JPanel(new FlowLayout(FlowLayout.LEFT, 5, 5));
        searchPanel.setBackground(new Color(245, 248, 250));
        searchPanel.setBorder(new LineBorder(new Color(220, 220, 220)));
        JLabel lblCari = new JLabel("  Cari:");
        lblCari.setFont(new Font("Segoe UI", Font.PLAIN, 12));
        searchPanel.add(lblCari);
        tfCari = new JTextField(25);
        tfCari.setFont(new Font("Segoe UI", Font.PLAIN, 12));
        tfCari.addKeyListener(new KeyAdapter() {
            @Override
            public void keyReleased(KeyEvent e) {
                loadData(tfCari.getText().trim());
            }
        });
        searchPanel.add(tfCari);
        JButton btnRefresh = new JButton("Refresh");
        btnRefresh.setBackground(new Color(149, 165, 166));
        btnRefresh.setForeground(Color.WHITE);
        btnRefresh.setFocusPainted(false);
        btnRefresh.setFont(new Font("Segoe UI", Font.BOLD, 11));
        btnRefresh.setBorder(BorderFactory.createEmptyBorder(8, 14, 8, 14));
        btnRefresh.setCursor(Cursor.getPredefinedCursor(Cursor.HAND_CURSOR));
        btnRefresh.addActionListener(e -> {
            tfCari.setText("");
            loadKelasCombo();
            loadData();
        });
        searchPanel.add(btnRefresh);
        centerPanel.add(searchPanel, BorderLayout.NORTH);

        model = new DefaultTableModel(
                new Object[]{"NIS", "Nama Siswa", "Jenis Kelamin", "Kelas", "Alamat", "No Telp"}, 0) {
            @Override
            public boolean isCellEditable(int row, int col) {
                return false;
            }
        };
        table = new JTable(model);
        table.setSelectionMode(ListSelectionModel.SINGLE_SELECTION);
        table.setRowHeight(28);
        table.setFont(new Font("Segoe UI", Font.PLAIN, 12));
        table.getTableHeader().setFont(new Font("Segoe UI", Font.BOLD, 12));
        table.getTableHeader().setBackground(new Color(41, 128, 185));
        table.getTableHeader().setForeground(Color.WHITE);
        table.addMouseListener(new MouseAdapter() {
            @Override
            public void mouseClicked(MouseEvent e) {
                fillFields();
            }
        });
        JScrollPane scroll = new JScrollPane(table);
        scroll.setBorder(new LineBorder(new Color(200, 200, 200)));
        centerPanel.add(scroll, BorderLayout.CENTER);
        add(centerPanel, BorderLayout.CENTER);

        // ── EAST: Input Form ──
        JPanel inputPanel = createInputPanel();
        add(inputPanel, BorderLayout.EAST);

        // ── Button Actions ──
        btnTambah.addActionListener(e -> tambahData());
        btnUbah.addActionListener(e -> ubahData());
        btnHapus.addActionListener(e -> hapusData());
        btnBersih.addActionListener(e -> bersihkan());
    }

    private JPanel createInputPanel() {
        JPanel panel = new JPanel(new GridBagLayout());
        panel.setBackground(new Color(245, 248, 250));
        panel.setBorder(BorderFactory.createCompoundBorder(
                new LineBorder(new Color(41, 128, 185), 1),
                BorderFactory.createEmptyBorder(15, 15, 15, 15)));
        panel.setPreferredSize(new Dimension(330, 0));

        GridBagConstraints gbc = new GridBagConstraints();
        gbc.insets = new Insets(6, 5, 6, 5);
        gbc.fill = GridBagConstraints.HORIZONTAL;

        String[] labels = {"NIS:", "Nama Siswa:", "Jenis Kelamin:", "Kelas:", "Alamat:", "No Telp:"};
        tfNis = new JTextField(20);
        tfNama = new JTextField(20);
        cbKelamin = new JComboBox<>(new String[]{"Laki-laki", "Perempuan"});
        cbKelas = new JComboBox<>();
        cbKelas.addItem("-- Pilih Kelas --");
        tfAlamat = new JTextField(20);
        tfTelp = new JTextField(20);
        JComponent[] fields = {tfNis, tfNama, cbKelamin, cbKelas, tfAlamat, tfTelp};

        for (int i = 0; i < labels.length; i++) {
            gbc.gridx = 0;
            gbc.gridy = i;
            gbc.weightx = 0;
            JLabel lbl = new JLabel(labels[i]);
            lbl.setFont(new Font("Segoe UI", Font.PLAIN, 12));
            panel.add(lbl, gbc);

            gbc.gridx = 1;
            gbc.weightx = 1.0;
            panel.add(fields[i], gbc);
        }

        // ── Buttons ──
        JPanel btnPanel = new JPanel(new GridLayout(2, 2, 8, 8));
        btnPanel.setBackground(new Color(245, 248, 250));

        btnTambah = createBtn("Tambah", new Color(46, 204, 113));
        btnUbah = createBtn("Ubah", new Color(241, 196, 15));
        btnHapus = createBtn("Hapus", new Color(231, 76, 60));
        btnBersih = createBtn("Bersih", new Color(149, 165, 166));

        btnPanel.add(btnTambah);
        btnPanel.add(btnUbah);
        btnPanel.add(btnHapus);
        btnPanel.add(btnBersih);

        gbc.gridx = 0;
        gbc.gridy = labels.length;
        gbc.gridwidth = 2;
        gbc.weightx = 1.0;
        panel.add(btnPanel, gbc);

        return panel;
    }

    private JButton createBtn(String text, Color bg) {
        JButton b = new JButton(text);
        b.setBackground(bg);
        b.setForeground(Color.WHITE);
        b.setFocusPainted(false);
        b.setFont(new Font("Segoe UI", Font.BOLD, 11));
        b.setBorder(BorderFactory.createEmptyBorder(8, 10, 8, 10));
        b.setCursor(Cursor.getPredefinedCursor(Cursor.HAND_CURSOR));
        return b;
    }

    // ─── DATABASE OPERATIONS ───

    private void loadKelasCombo() {
        cbKelas.removeAllItems();
        cbKelas.addItem("-- Pilih Kelas --");
        try {
            Connection conn = KoneksiDB.getKoneksi();
            PreparedStatement ps = conn.prepareStatement(
                    "SELECT id_kelas, nama_kelas FROM kelas ORDER BY nama_kelas");
            ResultSet rs = ps.executeQuery();
            while (rs.next()) {
                cbKelas.addItem(rs.getString("id_kelas") + " - " + rs.getString("nama_kelas"));
            }
            rs.close();
            ps.close();
        } catch (SQLException e) {
            JOptionPane.showMessageDialog(this, "Gagal load kelas: " + e.getMessage());
        }
    }

    private void loadData() {
        loadData("");
    }

    private void loadData(String keyword) {
        model.setRowCount(0);
        try {
            Connection conn = KoneksiDB.getKoneksi();
            String sql = "SELECT s.nis, s.nama_siswa, s.jenis_kelamin, "
                    + "CONCAT(s.id_kelas,' - ',k.nama_kelas) AS kelas, "
                    + "s.alamat, s.no_telp "
                    + "FROM siswa s LEFT JOIN kelas k ON s.id_kelas = k.id_kelas ";
            if (!keyword.isEmpty()) {
                sql += "WHERE s.nis LIKE ? OR s.nama_siswa LIKE ? ";
            }
            sql += "ORDER BY s.nama_siswa";
            PreparedStatement ps = conn.prepareStatement(sql);
            if (!keyword.isEmpty()) {
                ps.setString(1, "%" + keyword + "%");
                ps.setString(2, "%" + keyword + "%");
            }
            ResultSet rs = ps.executeQuery();
            while (rs.next()) {
                model.addRow(new Object[]{
                        rs.getString("nis"),
                        rs.getString("nama_siswa"),
                        rs.getString("jenis_kelamin"),
                        rs.getString("kelas"),
                        rs.getString("alamat"),
                        rs.getString("no_telp")
                });
            }
            rs.close();
            ps.close();
        } catch (SQLException e) {
            JOptionPane.showMessageDialog(this, "Gagal load data: " + e.getMessage());
        }
    }

    private void fillFields() {
        int row = table.getSelectedRow();
        if (row < 0) return;
        tfNis.setText(model.getValueAt(row, 0).toString());
        tfNis.setEditable(false);
        tfNama.setText(model.getValueAt(row, 1).toString());
        cbKelamin.setSelectedItem(model.getValueAt(row, 2).toString());
        String kelasVal = model.getValueAt(row, 3) != null ? model.getValueAt(row, 3).toString() : "";
        for (int i = 0; i < cbKelas.getItemCount(); i++) {
            if (cbKelas.getItemAt(i).equals(kelasVal)) {
                cbKelas.setSelectedIndex(i);
                break;
            }
        }
        tfAlamat.setText(model.getValueAt(row, 4) != null ? model.getValueAt(row, 4).toString() : "");
        tfTelp.setText(model.getValueAt(row, 5) != null ? model.getValueAt(row, 5).toString() : "");
        selectedNis = tfNis.getText();
    }

    private boolean validateInput() {
        if (tfNis.getText().trim().isEmpty()) {
            JOptionPane.showMessageDialog(this, "NIS harus diisi!"); tfNis.requestFocus(); return false;
        }
        if (tfNama.getText().trim().isEmpty()) {
            JOptionPane.showMessageDialog(this, "Nama harus diisi!"); tfNama.requestFocus(); return false;
        }
        if (cbKelas.getSelectedIndex() <= 0) {
            JOptionPane.showMessageDialog(this, "Pilih kelas!"); return false;
        }
        return true;
    }

    private String getSelectedIdKelas() {
        String val = cbKelas.getSelectedItem().toString();
        if (val.startsWith("--")) return null;
        return val.split(" - ")[0].trim();
    }

    private void tambahData() {
        if (!validateInput()) return;
        try {
            Connection conn = KoneksiDB.getKoneksi();
            PreparedStatement ps = conn.prepareStatement(
                    "INSERT INTO siswa (nis, nama_siswa, jenis_kelamin, id_kelas, alamat, no_telp) "
                    + "VALUES (?, ?, ?, ?, ?, ?)");
            ps.setString(1, tfNis.getText().trim());
            ps.setString(2, tfNama.getText().trim());
            ps.setString(3, cbKelamin.getSelectedItem().toString());
            ps.setString(4, getSelectedIdKelas());
            ps.setString(5, tfAlamat.getText().trim());
            ps.setString(6, tfTelp.getText().trim());
            if (ps.executeUpdate() > 0) {
                JOptionPane.showMessageDialog(this, "Data siswa berhasil ditambahkan!");
                loadData();
                bersihkan();
            }
            ps.close();
        } catch (SQLIntegrityConstraintViolationException e) {
            JOptionPane.showMessageDialog(this, "NIS sudah ada di database!");
        } catch (SQLException e) {
            JOptionPane.showMessageDialog(this, "Gagal tambah: " + e.getMessage());
        }
    }

    private void ubahData() {
        if (selectedNis == null) {
            JOptionPane.showMessageDialog(this, "Pilih data yang akan diubah!");
            return;
        }
        if (!validateInput()) return;
        try {
            Connection conn = KoneksiDB.getKoneksi();
            PreparedStatement ps = conn.prepareStatement(
                    "UPDATE siswa SET nama_siswa=?, jenis_kelamin=?, id_kelas=?, alamat=?, no_telp=? "
                    + "WHERE nis=?");
            ps.setString(1, tfNama.getText().trim());
            ps.setString(2, cbKelamin.getSelectedItem().toString());
            ps.setString(3, getSelectedIdKelas());
            ps.setString(4, tfAlamat.getText().trim());
            ps.setString(5, tfTelp.getText().trim());
            ps.setString(6, selectedNis);
            if (ps.executeUpdate() > 0) {
                JOptionPane.showMessageDialog(this, "Data siswa berhasil diubah!");
                loadData();
                bersihkan();
            }
            ps.close();
        } catch (SQLException e) {
            JOptionPane.showMessageDialog(this, "Gagal ubah: " + e.getMessage());
        }
    }

    private void hapusData() {
        if (selectedNis == null) {
            JOptionPane.showMessageDialog(this, "Pilih data yang akan dihapus!");
            return;
        }
        int c = JOptionPane.showConfirmDialog(this,
                "Hapus siswa NIS " + selectedNis + "?", "Konfirmasi", JOptionPane.YES_NO_OPTION);
        if (c == JOptionPane.YES_OPTION) {
            try {
                Connection conn = KoneksiDB.getKoneksi();
                PreparedStatement ps = conn.prepareStatement("DELETE FROM siswa WHERE nis=?");
                ps.setString(1, selectedNis);
                if (ps.executeUpdate() > 0) {
                    JOptionPane.showMessageDialog(this, "Data berhasil dihapus!");
                    loadData();
                    bersihkan();
                }
                ps.close();
            } catch (SQLException e) {
                JOptionPane.showMessageDialog(this, "Gagal hapus: " + e.getMessage());
            }
        }
    }

    private void bersihkan() {
        tfNis.setText("");
        tfNis.setEditable(true);
        tfNama.setText("");
        cbKelamin.setSelectedIndex(0);
        cbKelas.setSelectedIndex(0);
        tfAlamat.setText("");
        tfTelp.setText("");
        selectedNis = null;
        table.clearSelection();
    }
}
