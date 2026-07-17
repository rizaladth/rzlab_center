package forms;

import java.awt.*;
import java.awt.event.*;
import java.sql.*;
import javax.swing.*;
import javax.swing.table.*;
import javax.swing.border.*;
import koneksi.KoneksiDB;

public class FormKelas extends JPanel {

    private JTable table;
    private DefaultTableModel model;
    private JTextField tfIdKelas, tfNamaKelas, tfCari;
    private JComboBox<String> cbWaliKelas;
    private JButton btnSimpan, btnEdit, btnHapus, btnReset;
    private String selectedId = null;

    public FormKelas() {
        initComponents();
        loadWaliCombo();
        loadData();
    }

    private void initComponents() {
        setLayout(new BorderLayout(10, 10));
        setBackground(Color.WHITE);
        setBorder(BorderFactory.createEmptyBorder(15, 15, 15, 15));

        // ── HEADER ──
        JLabel lblTitle = new JLabel("Manajemen Data Kelas");
        lblTitle.setFont(new Font("Segoe UI", Font.BOLD, 22));
        lblTitle.setForeground(new Color(41, 128, 185));
        add(lblTitle, BorderLayout.NORTH);

        // ── CENTER: Search + Table ──
        JPanel centerPanel = new JPanel(new BorderLayout(5, 5));
        centerPanel.setBackground(Color.WHITE);

        JPanel searchPanel = new JPanel(new FlowLayout(FlowLayout.LEFT, 5, 5));
        searchPanel.setBackground(new Color(245, 248, 250));
        searchPanel.setBorder(new LineBorder(new Color(220, 220, 220)));
        searchPanel.add(new JLabel("  Cari:"));
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
            loadWaliCombo();
            loadData();
        });
        searchPanel.add(btnRefresh);
        centerPanel.add(searchPanel, BorderLayout.NORTH);

        model = new DefaultTableModel(
                new Object[]{"ID Kelas", "Nama Kelas", "Wali Kelas"}, 0) {
            @Override
            public boolean isCellEditable(int row, int col) { return false; }
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
            public void mouseClicked(MouseEvent e) { fillFields(); }
        });
        JScrollPane scroll = new JScrollPane(table);
        scroll.setBorder(new LineBorder(new Color(200, 200, 200)));
        centerPanel.add(scroll, BorderLayout.CENTER);
        add(centerPanel, BorderLayout.CENTER);

        // ── EAST: Input Form ──
        add(createInputPanel(), BorderLayout.EAST);

        btnSimpan.addActionListener(e -> simpanData());
        btnEdit.addActionListener(e -> editData());
        btnHapus.addActionListener(e -> hapusData());
        btnReset.addActionListener(e -> resetForm());
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

        String[] labels = {"ID Kelas:", "Nama Kelas:", "Wali Kelas:"};
        tfIdKelas = new JTextField(20);
        tfNamaKelas = new JTextField(20);
        cbWaliKelas = new JComboBox<>();
        cbWaliKelas.addItem("-- Pilih Wali Kelas --");
        JComponent[] fields = {tfIdKelas, tfNamaKelas, cbWaliKelas};

        for (int i = 0; i < labels.length; i++) {
            gbc.gridx = 0; gbc.gridy = i; gbc.weightx = 0;
            JLabel lbl = new JLabel(labels[i]);
            lbl.setFont(new Font("Segoe UI", Font.PLAIN, 12));
            panel.add(lbl, gbc);
            gbc.gridx = 1; gbc.weightx = 1.0;
            panel.add(fields[i], gbc);
        }

        JPanel btnPanel = new JPanel(new GridLayout(2, 2, 8, 8));
        btnPanel.setBackground(new Color(245, 248, 250));
        btnSimpan = createBtn("Simpan", new Color(46, 204, 113));
        btnEdit = createBtn("Edit", new Color(241, 196, 15));
        btnHapus = createBtn("Hapus", new Color(231, 76, 60));
        btnReset = createBtn("Reset", new Color(149, 165, 166));
        btnPanel.add(btnSimpan);
        btnPanel.add(btnEdit);
        btnPanel.add(btnHapus);
        btnPanel.add(btnReset);

        gbc.gridx = 0; gbc.gridy = labels.length;
        gbc.gridwidth = 2; gbc.weightx = 1.0;
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

    private void loadWaliCombo() {
        cbWaliKelas.removeAllItems();
        cbWaliKelas.addItem("-- Pilih Wali Kelas --");
        try {
            Connection conn = KoneksiDB.getKoneksi();
            PreparedStatement ps = conn.prepareStatement(
                    "SELECT nama_guru FROM guru ORDER BY nama_guru");
            ResultSet rs = ps.executeQuery();
            while (rs.next()) {
                cbWaliKelas.addItem(rs.getString("nama_guru"));
            }
            rs.close();
            ps.close();
        } catch (SQLException e) {
            JOptionPane.showMessageDialog(this, "Gagal load guru: " + e.getMessage());
        }
    }

    private void loadData() {
        loadData("");
    }

    private void loadData(String keyword) {
        model.setRowCount(0);
        try {
            Connection conn = KoneksiDB.getKoneksi();
            String sql = "SELECT id_kelas, nama_kelas, wali_kelas FROM kelas ";
            if (!keyword.isEmpty()) {
                sql += "WHERE id_kelas LIKE ? OR nama_kelas LIKE ? OR wali_kelas LIKE ? ";
            }
            sql += "ORDER BY id_kelas";
            PreparedStatement ps = conn.prepareStatement(sql);
            if (!keyword.isEmpty()) {
                ps.setString(1, "%" + keyword + "%");
                ps.setString(2, "%" + keyword + "%");
                ps.setString(3, "%" + keyword + "%");
            }
            ResultSet rs = ps.executeQuery();
            while (rs.next()) {
                model.addRow(new Object[]{
                        rs.getString("id_kelas"),
                        rs.getString("nama_kelas"),
                        rs.getString("wali_kelas")
                });
            }
            rs.close();
            ps.close();
        } catch (SQLException e) {
            JOptionPane.showMessageDialog(this, "Gagal load: " + e.getMessage());
        }
    }

    private void fillFields() {
        int row = table.getSelectedRow();
        if (row < 0) return;
        tfIdKelas.setText(model.getValueAt(row, 0).toString());
        tfIdKelas.setEditable(false);
        tfNamaKelas.setText(model.getValueAt(row, 1).toString());
        String wali = model.getValueAt(row, 2) != null ? model.getValueAt(row, 2).toString() : "";
        cbWaliKelas.setSelectedItem(wali);
        selectedId = tfIdKelas.getText();
    }

    private boolean validateInput() {
        if (tfIdKelas.getText().trim().isEmpty()) {
            JOptionPane.showMessageDialog(this, "ID Kelas harus diisi!"); tfIdKelas.requestFocus(); return false;
        }
        if (tfNamaKelas.getText().trim().isEmpty()) {
            JOptionPane.showMessageDialog(this, "Nama Kelas harus diisi!"); tfNamaKelas.requestFocus(); return false;
        }
        return true;
    }

    private void simpanData() {
        if (!validateInput()) return;
        try {
            Connection conn = KoneksiDB.getKoneksi();
            PreparedStatement ps = conn.prepareStatement(
                    "INSERT INTO kelas (id_kelas, nama_kelas, wali_kelas) VALUES (?, ?, ?)");
            ps.setString(1, tfIdKelas.getText().trim());
            ps.setString(2, tfNamaKelas.getText().trim());
            ps.setString(3, cbWaliKelas.getSelectedIndex() > 0
                    ? cbWaliKelas.getSelectedItem().toString() : null);
            if (ps.executeUpdate() > 0) {
                JOptionPane.showMessageDialog(this, "Kelas berhasil disimpan!");
                loadData();
                resetForm();
            }
            ps.close();
        } catch (SQLIntegrityConstraintViolationException e) {
            JOptionPane.showMessageDialog(this, "ID Kelas sudah ada!");
        } catch (SQLException e) {
            JOptionPane.showMessageDialog(this, "Gagal simpan: " + e.getMessage());
        }
    }

    private void editData() {
        if (selectedId == null) {
            JOptionPane.showMessageDialog(this, "Pilih data yang akan diedit!");
            return;
        }
        if (!validateInput()) return;
        try {
            Connection conn = KoneksiDB.getKoneksi();
            PreparedStatement ps = conn.prepareStatement(
                    "UPDATE kelas SET nama_kelas=?, wali_kelas=? WHERE id_kelas=?");
            ps.setString(1, tfNamaKelas.getText().trim());
            ps.setString(2, cbWaliKelas.getSelectedIndex() > 0
                    ? cbWaliKelas.getSelectedItem().toString() : null);
            ps.setString(3, selectedId);
            if (ps.executeUpdate() > 0) {
                JOptionPane.showMessageDialog(this, "Kelas berhasil diubah!");
                loadData();
                resetForm();
            }
            ps.close();
        } catch (SQLException e) {
            JOptionPane.showMessageDialog(this, "Gagal edit: " + e.getMessage());
        }
    }

    private void hapusData() {
        if (selectedId == null) {
            JOptionPane.showMessageDialog(this, "Pilih data yang akan dihapus!");
            return;
        }
        int c = JOptionPane.showConfirmDialog(this,
                "Hapus kelas " + selectedId + "?",
                "Konfirmasi", JOptionPane.YES_NO_OPTION);
        if (c == JOptionPane.YES_OPTION) {
            try {
                Connection conn = KoneksiDB.getKoneksi();
                PreparedStatement ps = conn.prepareStatement("DELETE FROM kelas WHERE id_kelas=?");
                ps.setString(1, selectedId);
                if (ps.executeUpdate() > 0) {
                    JOptionPane.showMessageDialog(this, "Kelas berhasil dihapus!");
                    loadData();
                    resetForm();
                }
                ps.close();
            } catch (SQLException e) {
                JOptionPane.showMessageDialog(this, "Gagal hapus: " + e.getMessage());
            }
        }
    }

    private void resetForm() {
        tfIdKelas.setText("");
        tfIdKelas.setEditable(true);
        tfNamaKelas.setText("");
        cbWaliKelas.setSelectedIndex(0);
        selectedId = null;
        table.clearSelection();
    }
}
