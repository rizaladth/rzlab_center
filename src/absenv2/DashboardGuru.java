package absenv2;

import forms.*;
import java.awt.*;
import java.awt.event.*;
import javax.swing.*;
import javax.swing.border.*;
import koneksi.KoneksiDB;

public class DashboardGuru extends JFrame {

    private JPanel sidebarPanel, mainPanel, contentPanel;
    private CardLayout cardLayout;
    private JButton selectedBtn = null;
    private String username;
    private Color defaultSidebarBg = new Color(44, 62, 80);
    private Color activeBtnBg = new Color(52, 152, 219);
    private Color hoverBtnBg = new Color(57, 79, 101);

    public DashboardGuru(String username) {
        this.username = username;
        setTitle("AbsenV2 - Dashboard Guru");
        setSize(1280, 720);
        setMinimumSize(new Dimension(1024, 600));
        setDefaultCloseOperation(EXIT_ON_CLOSE);
        setLocationRelativeTo(null);
        initComponents();
    }

    private void initComponents() {
        getContentPane().setLayout(new BorderLayout());
        getContentPane().setBackground(Color.WHITE);

        // === Sidebar ===
        sidebarPanel = new JPanel();
        sidebarPanel.setLayout(new BoxLayout(sidebarPanel, BoxLayout.Y_AXIS));
        sidebarPanel.setBackground(defaultSidebarBg);
        sidebarPanel.setPreferredSize(new Dimension(240, 0));
        sidebarPanel.setBorder(BorderFactory.createEmptyBorder(0, 0, 0, 0));

        // Logo / App Name
        JPanel logoPanel = new JPanel();
        logoPanel.setBackground(new Color(44, 62, 80));
        logoPanel.setLayout(new BorderLayout());
        logoPanel.setBorder(BorderFactory.createEmptyBorder(20, 15, 20, 15));
        logoPanel.setMaximumSize(new Dimension(240, 80));

        JLabel lblLogo = new JLabel("AbsenV2");
        lblLogo.setFont(new Font("Segoe UI", Font.BOLD, 24));
        lblLogo.setForeground(Color.WHITE);
        lblLogo.setHorizontalAlignment(SwingConstants.CENTER);
        logoPanel.add(lblLogo, BorderLayout.CENTER);
        sidebarPanel.add(logoPanel);

        // Separator
        JSeparator sep1 = new JSeparator();
        sep1.setMaximumSize(new Dimension(240, 2));
        sep1.setForeground(new Color(52, 73, 94));
        sidebarPanel.add(sep1);

        // Navigation label
        JLabel lblNav = new JLabel("  MENU NAVIGASI");
        lblNav.setFont(new Font("Segoe UI", Font.PLAIN, 10));
        lblNav.setForeground(new Color(149, 165, 166));
        lblNav.setMaximumSize(new Dimension(240, 30));
        lblNav.setBorder(BorderFactory.createEmptyBorder(15, 10, 5, 10));
        sidebarPanel.add(lblNav);

        // Nav buttons
        String[][] menus = {
            {"Siswa", "Manajemen Siswa"},
            {"Kelas", "Manajemen Kelas"},
            {"Laporan", "Laporan"},
            {"Transaksi", "Transaksi"},
            {"Pengaturan", "Pengaturan"}
        };

        ButtonGroup navGroup = new ButtonGroup();
        JButton firstBtn = null;

        for (String[] menu : menus) {
            JButton btn = createNavButton(menu[0], menu[1]);
            navGroup.add(btn);
            sidebarPanel.add(btn);
            if (firstBtn == null) firstBtn = btn;
        }

        // Spacer
        sidebarPanel.add(Box.createVerticalGlue());

        // User info at bottom
        JPanel userPanel = new JPanel(new BorderLayout());
        userPanel.setBackground(new Color(38, 52, 69));
        userPanel.setMaximumSize(new Dimension(240, 60));
        userPanel.setBorder(BorderFactory.createEmptyBorder(10, 15, 10, 15));

        JLabel lblUser = new JLabel("Guru: " + username);
        lblUser.setFont(new Font("Segoe UI", Font.PLAIN, 12));
        lblUser.setForeground(Color.WHITE);
        userPanel.add(lblUser, BorderLayout.NORTH);

        JButton btnLogout = new JButton("Logout");
        btnLogout.setBackground(new Color(231, 76, 60));
        btnLogout.setForeground(Color.WHITE);
        btnLogout.setFocusPainted(false);
        btnLogout.setFont(new Font("Segoe UI", Font.BOLD, 10));
        btnLogout.setBorder(BorderFactory.createEmptyBorder(5, 15, 5, 15));
        btnLogout.setCursor(Cursor.getPredefinedCursor(Cursor.HAND_CURSOR));
        btnLogout.addActionListener(e -> {
            int confirm = JOptionPane.showConfirmDialog(this,
                    "Yakin ingin logout?", "Logout",
                    JOptionPane.YES_NO_OPTION);
            if (confirm == JOptionPane.YES_OPTION) {
                KoneksiDB.closeKoneksi();
                dispose();
                new LoginFrame().setVisible(true);
            }
        });
        userPanel.add(btnLogout, BorderLayout.EAST);
        sidebarPanel.add(userPanel);

        // === Main Content Area ===
        cardLayout = new CardLayout();
        contentPanel = new JPanel(cardLayout);
        contentPanel.setBackground(Color.WHITE);

        // Add form panels
        contentPanel.add(new FormSiswa(), "SISWA");
        contentPanel.add(new FormKelas(), "KELAS");
        contentPanel.add(new FormLaporan(), "LAPORAN");
        contentPanel.add(new FormTransaksi(), "TRANSAKSI");
        contentPanel.add(new FormPengaturan(username), "PENGATURAN");

        // === Assemble ===
        getContentPane().add(sidebarPanel, BorderLayout.WEST);
        getContentPane().add(contentPanel, BorderLayout.CENTER);

        // Activate first menu
        if (firstBtn != null) {
            firstBtn.doClick();
        }
    }

    private JButton createNavButton(String key, String label) {
        JButton btn = new JButton("    " + label);
        btn.setFont(new Font("Segoe UI", Font.PLAIN, 14));
        btn.setForeground(new Color(189, 195, 199));
        btn.setBackground(defaultSidebarBg);
        btn.setBorderPainted(false);
        btn.setFocusPainted(false);
        btn.setHorizontalAlignment(SwingConstants.LEFT);
        btn.setCursor(Cursor.getPredefinedCursor(Cursor.HAND_CURSOR));
        btn.setMaximumSize(new Dimension(240, 45));
        btn.setPreferredSize(new Dimension(240, 45));
        btn.setBorder(BorderFactory.createEmptyBorder(10, 20, 10, 10));

        final String cardKey = key.toUpperCase();

        btn.addMouseListener(new MouseAdapter() {
            @Override
            public void mouseEntered(MouseEvent e) {
                if (btn != selectedBtn) {
                    btn.setBackground(hoverBtnBg);
                }
            }

            @Override
            public void mouseExited(MouseEvent e) {
                if (btn != selectedBtn) {
                    btn.setBackground(defaultSidebarBg);
                }
            }
        });

        btn.addActionListener(e -> {
            if (selectedBtn != null) {
                selectedBtn.setBackground(defaultSidebarBg);
                selectedBtn.setForeground(new Color(189, 195, 199));
            }
            selectedBtn = btn;
            btn.setBackground(activeBtnBg);
            btn.setForeground(Color.WHITE);
            cardLayout.show(contentPanel, cardKey);
        });

        return btn;
    }

    // === LOGIN FRAME (inner class for demo) ===
    public static class LoginFrame extends JFrame {

        private JTextField tfUsername;
        private JPasswordField pfPassword;

        public LoginFrame() {
            setTitle("AbsenV2 - Login");
            setSize(400, 350);
            setDefaultCloseOperation(EXIT_ON_CLOSE);
            setLocationRelativeTo(null);
            setResizable(false);
            initLoginUI();
        }

        private void initLoginUI() {
            JPanel panel = new JPanel(new GridBagLayout());
            panel.setBackground(new Color(245, 248, 250));
            panel.setBorder(BorderFactory.createEmptyBorder(30, 40, 30, 40));
            GridBagConstraints gbc = new GridBagConstraints();
            gbc.insets = new Insets(8, 5, 8, 5);
            gbc.fill = GridBagConstraints.HORIZONTAL;

            JLabel lblTitle = new JLabel("AbsenV2");
            lblTitle.setFont(new Font("Segoe UI", Font.BOLD, 28));
            lblTitle.setForeground(new Color(41, 128, 185));
            lblTitle.setHorizontalAlignment(SwingConstants.CENTER);
            gbc.gridx = 0; gbc.gridy = 0; gbc.gridwidth = 2;
            panel.add(lblTitle, gbc);

            JLabel lblSub = new JLabel("Silakan masuk ke akun Anda");
            lblSub.setFont(new Font("Segoe UI", Font.PLAIN, 12));
            lblSub.setForeground(Color.GRAY);
            lblSub.setHorizontalAlignment(SwingConstants.CENTER);
            gbc.gridy = 1;
            panel.add(lblSub, gbc);

            gbc.gridwidth = 1;
            gbc.gridy = 2;
            gbc.gridx = 0;
            panel.add(new JLabel("Username:"), gbc);
            tfUsername = new JTextField(20);
            gbc.gridx = 1;
            panel.add(tfUsername, gbc);

            gbc.gridy = 3;
            gbc.gridx = 0;
            panel.add(new JLabel("Password:"), gbc);
            pfPassword = new JPasswordField(20);
            gbc.gridx = 1;
            panel.add(pfPassword, gbc);

            JButton btnLogin = new JButton("Masuk");
            btnLogin.setBackground(new Color(41, 128, 185));
            btnLogin.setForeground(Color.WHITE);
            btnLogin.setFocusPainted(false);
            btnLogin.setFont(new Font("Segoe UI", Font.BOLD, 13));
            btnLogin.setBorder(BorderFactory.createEmptyBorder(10, 30, 10, 30));
            btnLogin.setCursor(Cursor.getPredefinedCursor(Cursor.HAND_CURSOR));
            gbc.gridy = 4;
            gbc.gridx = 0;
            gbc.gridwidth = 2;
            panel.add(btnLogin, gbc);

            btnLogin.addActionListener(e -> doLogin());
            pfPassword.addActionListener(e -> doLogin());

            setContentPane(panel);
        }

        private void doLogin() {
            String user = tfUsername.getText().trim();
            String pass = new String(pfPassword.getPassword());

            if (user.isEmpty() || pass.isEmpty()) {
                JOptionPane.showMessageDialog(this, "Username dan password harus diisi!");
                return;
            }

            try {
                java.sql.Connection conn = KoneksiDB.getKoneksi();
                String sql = "SELECT * FROM guru WHERE username = ? AND password = ?";
                java.sql.PreparedStatement ps = conn.prepareStatement(sql);
                ps.setString(1, user);
                ps.setString(2, pass);
                java.sql.ResultSet rs = ps.executeQuery();

                if (rs.next()) {
                    String namaGuru = rs.getString("nama_guru");
                    dispose();
                    SwingUtilities.invokeLater(() -> {
                        DashboardGuru dashboard = new DashboardGuru(user);
                        dashboard.setVisible(true);
                    });
                } else {
                    JOptionPane.showMessageDialog(this,
                            "Username atau password salah!",
                            "Login Gagal", JOptionPane.ERROR_MESSAGE);
                }
                rs.close();
                ps.close();
            } catch (java.sql.SQLException ex) {
                JOptionPane.showMessageDialog(this,
                        "Error database: " + ex.getMessage(),
                        "Login Gagal", JOptionPane.ERROR_MESSAGE);
            }
        }
    }

    // === MAIN ===
    public static void main(String[] args) {
        try {
            UIManager.setLookAndFeel(UIManager.getSystemLookAndFeelClassName());
        } catch (Exception e) {
            e.printStackTrace();
        }

        SwingUtilities.invokeLater(() -> {
            new LoginFrame().setVisible(true);
        });
    }
}
