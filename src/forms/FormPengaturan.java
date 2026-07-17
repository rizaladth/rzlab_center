package forms;

import java.awt.*;
import java.sql.*;
import javax.swing.*;
import javax.swing.border.*;
import koneksi.KoneksiDB;
import koneksi.PasswordUtil;

public class FormPengaturan extends JPanel {

    private JTextField tfNamaUser, tfEmail;
    private JPasswordField pfPassLama, pfPassBaru, tfPassConfirm;
    private JTextField tfNamaSekolah, tfAlamatSekolah, tfTelpSekolah, tfEmailSekolah;
    private JButton btnSimpan;
    private String currentUser;

    public FormPengaturan() {
        this.currentUser = null;
        initComponents();
        loadProfile();
        loadPengaturan();
    }

    public FormPengaturan(String username) {
        this.currentUser = username;
        initComponents();
        loadProfile();
        loadPengaturan();
    }

    private void initComponents() {
        setLayout(new BorderLayout(10, 10));
        setBackground(Color.WHITE);
        setBorder(BorderFactory.createEmptyBorder(15, 15, 15, 15));

        // ── HEADER ──
        JLabel lblTitle = new JLabel("Pengaturan");
        lblTitle.setFont(new Font("Segoe UI", Font.BOLD, 22));
        lblTitle.setForeground(new Color(41, 128, 185));
        add(lblTitle, BorderLayout.NORTH);

        // ── CONTENT: Two panels side by side ──
        JPanel contentPanel = new JPanel(new GridLayout(1, 2, 15, 0));
        contentPanel.setBackground(Color.WHITE);
        contentPanel.add(buildProfilPanel());
        contentPanel.add(buildAppPanel());
        add(contentPanel, BorderLayout.CENTER);

        // ── BOTTOM: Save Button ──
        JPanel bottomPanel = new JPanel(new FlowLayout(FlowLayout.CENTER));
        bottomPanel.setBackground(Color.WHITE);
        btnSimpan = new JButton("Simpan Perubahan");
        btnSimpan.setBackground(new Color(46, 204, 113));
        btnSimpan.setForeground(Color.WHITE);
        btnSimpan.setFocusPainted(false);
        btnSimpan.setFont(new Font("Segoe UI", Font.BOLD, 13));
        btnSimpan.setBorder(BorderFactory.createEmptyBorder(10, 40, 10, 40));
        btnSimpan.setCursor(Cursor.getPredefinedCursor(Cursor.HAND_CURSOR));
        btnSimpan.addActionListener(e -> simpanSemua());
        bottomPanel.add(btnSimpan);
        add(bottomPanel, BorderLayout.SOUTH);
    }

    private JPanel buildProfilPanel() {
        JPanel p = new JPanel(new GridBagLayout());
        p.setBackground(new Color(245, 248, 250));
        p.setBorder(BorderFactory.createCompoundBorder(
                new LineBorder(new Color(41, 128, 185)),
                BorderFactory.createEmptyBorder(15, 15, 15, 15)));

        GridBagConstraints gbc = new GridBagConstraints();
        gbc.insets = new Insets(6, 5, 6, 5);
        gbc.fill = GridBagConstraints.HORIZONTAL;
        gbc.anchor = GridBagConstraints.WEST;

        tfNamaUser = new JTextField(22);
        tfEmail = new JTextField(22);
        pfPassLama = new JPasswordField(22);
        pfPassBaru = new JPasswordField(22);
        tfPassConfirm = new JPasswordField(22);

        String[] labels = {"Nama Lengkap:", "Email:", "Password Lama:", "Password Baru:", "Konfirmasi Password:"};
        JComponent[] fields = {tfNamaUser, tfEmail, pfPassLama, pfPassBaru, tfPassConfirm};

        for (int i = 0; i < labels.length; i++) {
            gbc.gridx = 0; gbc.gridy = i; gbc.weightx = 0;
            JLabel lbl = new JLabel(labels[i]);
            lbl.setFont(new Font("Segoe UI", Font.PLAIN, 12));
            p.add(lbl, gbc);
            gbc.gridx = 1; gbc.weightx = 1.0;
            p.add(fields[i], gbc);
        }
        return p;
    }

    private JPanel buildAppPanel() {
        JPanel p = new JPanel(new GridBagLayout());
        p.setBackground(new Color(245, 248, 250));
        p.setBorder(BorderFactory.createCompoundBorder(
                new LineBorder(new Color(41, 128, 185)),
                BorderFactory.createEmptyBorder(15, 15, 15, 15)));

        GridBagConstraints gbc = new GridBagConstraints();
        gbc.insets = new Insets(6, 5, 6, 5);
        gbc.fill = GridBagConstraints.HORIZONTAL;

        tfNamaSekolah = new JTextField(22);
        tfAlamatSekolah = new JTextField(22);
        tfTelpSekolah = new JTextField(22);
        tfEmailSekolah = new JTextField(22);

        String[] labels = {"Nama Sekolah:", "Alamat Sekolah:", "Telp Sekolah:", "Email Sekolah:"};
        JComponent[] fields = {tfNamaSekolah, tfAlamatSekolah, tfTelpSekolah, tfEmailSekolah};

        for (int i = 0; i < labels.length; i++) {
            gbc.gridx = 0; gbc.gridy = i; gbc.weightx = 0;
            JLabel lbl = new JLabel(labels[i]);
            lbl.setFont(new Font("Segoe UI", Font.PLAIN, 12));
            p.add(lbl, gbc);
            gbc.gridx = 1; gbc.weightx = 1.0;
            p.add(fields[i], gbc);
        }
        return p;
    }

    // ─── DATABASE OPERATIONS ───

    private void loadProfile() {
        if (currentUser == null || currentUser.isEmpty()) return;
        try {
            Connection conn = KoneksiDB.getKoneksi();
            PreparedStatement ps = conn.prepareStatement(
                    "SELECT nama_guru, email FROM guru WHERE username = ?");
            ps.setString(1, currentUser);
            ResultSet rs = ps.executeQuery();
            if (rs.next()) {
                tfNamaUser.setText(rs.getString("nama_guru") != null ? rs.getString("nama_guru") : "");
                tfEmail.setText(rs.getString("email") != null ? rs.getString("email") : "");
            }
            rs.close(); ps.close();
        } catch (SQLException e) {
            JOptionPane.showMessageDialog(this, "Gagal load profil: " + e.getMessage());
        }
    }

    private void loadPengaturan() {
        try {
            Connection conn = KoneksiDB.getKoneksi();
            PreparedStatement ps = conn.prepareStatement(
                    "SELECT nama_sekolah, alamat_sekolah, telp_sekolah, email_sekolah "
                    + "FROM pengaturan ORDER BY id_pengaturan LIMIT 1");
            ResultSet rs = ps.executeQuery();
            if (rs.next()) {
                tfNamaSekolah.setText(rs.getString("nama_sekolah") != null ? rs.getString("nama_sekolah") : "");
                tfAlamatSekolah.setText(rs.getString("alamat_sekolah") != null ? rs.getString("alamat_sekolah") : "");
                tfTelpSekolah.setText(rs.getString("telp_sekolah") != null ? rs.getString("telp_sekolah") : "");
                tfEmailSekolah.setText(rs.getString("email_sekolah") != null ? rs.getString("email_sekolah") : "");
            }
            rs.close(); ps.close();
        } catch (SQLException e) {
            JOptionPane.showMessageDialog(this, "Gagal load pengaturan: " + e.getMessage());
        }
    }

    private boolean updateProfile() {
        if (currentUser == null || currentUser.isEmpty()) return true;

        String nama = tfNamaUser.getText().trim();
        String email = tfEmail.getText().trim();
        String passLama = new String(pfPassLama.getPassword());
        String passBaru = new String(pfPassBaru.getPassword());
        String passConfirm = new String(tfPassConfirm.getPassword());

        if (nama.isEmpty()) {
            JOptionPane.showMessageDialog(this, "Nama tidak boleh kosong!");
            return false;
        }

        try {
            Connection conn = KoneksiDB.getKoneksi();

            if (!passBaru.isEmpty()) {
                if (passLama.isEmpty()) {
                    JOptionPane.showMessageDialog(this, "Isi password lama terlebih dahulu!");
                    return false;
                }
                if (!passBaru.equals(passConfirm)) {
                    JOptionPane.showMessageDialog(this, "Konfirmasi password tidak cocok!");
                    return false;
                }
                if (passBaru.length() < 6) {
                    JOptionPane.showMessageDialog(this, "Password baru minimal 6 karakter!");
                    return false;
                }

                // Verify old password
                PreparedStatement psV = conn.prepareStatement(
                        "SELECT password FROM guru WHERE username = ?");
                psV.setString(1, currentUser);
                ResultSet rsV = psV.executeQuery();
                if (rsV.next()) {
                    String stored = rsV.getString("password");
                    if (!PasswordUtil.checkPassword(passLama, stored)) {
                        JOptionPane.showMessageDialog(this, "Password lama salah!");
                        rsV.close(); psV.close();
                        return false;
                    }
                }
                rsV.close(); psV.close();

                PreparedStatement ps = conn.prepareStatement(
                        "UPDATE guru SET nama_guru=?, email=?, password=? WHERE username=?");
                ps.setString(1, nama);
                ps.setString(2, email);
                ps.setString(3, PasswordUtil.hashPassword(passBaru));
                ps.setString(4, currentUser);
                ps.executeUpdate(); ps.close();
            } else {
                PreparedStatement ps = conn.prepareStatement(
                        "UPDATE guru SET nama_guru=?, email=? WHERE username=?");
                ps.setString(1, nama);
                ps.setString(2, email);
                ps.setString(3, currentUser);
                ps.executeUpdate(); ps.close();
            }

            pfPassLama.setText("");
            pfPassBaru.setText("");
            tfPassConfirm.setText("");
            return true;
        } catch (SQLException e) {
            JOptionPane.showMessageDialog(this, "Gagal update profil: " + e.getMessage());
            return false;
        }
    }

    private void updatePengaturan() {
        try {
            Connection conn = KoneksiDB.getKoneksi();
            PreparedStatement ps = conn.prepareStatement(
                    "UPDATE pengaturan SET nama_sekolah=?, alamat_sekolah=?, "
                    + "telp_sekolah=?, email_sekolah=? WHERE id_pengaturan=1");
            ps.setString(1, tfNamaSekolah.getText().trim());
            ps.setString(2, tfAlamatSekolah.getText().trim());
            ps.setString(3, tfTelpSekolah.getText().trim());
            ps.setString(4, tfEmailSekolah.getText().trim());
            ps.executeUpdate(); ps.close();
        } catch (SQLException e) {
            JOptionPane.showMessageDialog(this, "Gagal update pengaturan: " + e.getMessage());
        }
    }

    private void simpanSemua() {
        int c = JOptionPane.showConfirmDialog(this,
                "Simpan semua perubahan?", "Konfirmasi", JOptionPane.YES_NO_OPTION);
        if (c == JOptionPane.YES_OPTION) {
            if (updateProfile()) {
                updatePengaturan();
                JOptionPane.showMessageDialog(this, "Semua pengaturan berhasil disimpan!");
            }
        }
    }
}
