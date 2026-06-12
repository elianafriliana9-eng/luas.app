import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

class NotificationScreen extends StatelessWidget {
  const NotificationScreen({super.key});

  static const Color surface = Color(0xFFF8FAFC);
  static const Color primaryBlue = Color(0xFF1D4ED8); // blue-700
  static const Color successGreen = Color(0xFF059669); // emerald-600
  static const Color dangerRed = Color(0xFFDC2626); // red-600
  static const Color warningOrange = Color(0xFFF59E0B); // amber-500

  @override
  Widget build(BuildContext context) {
    // Dummy data untuk visualisasi Frontend
    final List<Map<String, dynamic>> dummyNotifications = [
      {
        'id': 1,
        'type': 'overdue',
        'title': 'Tagihan Jatuh Tempo',
        'message': 'Angsuran ke-3 untuk Pinjaman Karyawan (Rp 1.500.000) telah melewati batas jatuh tempo. Segera lakukan pembayaran.',
        'time': '2 jam yang lalu',
        'is_read': false,
      },
      {
        'id': 2,
        'type': 'upcoming',
        'title': 'Pengingat Tagihan',
        'message': 'Angsuran Pinjaman Anda akan jatuh tempo dalam 3 hari. Saldo akan otomatis dipotong dari payroll.',
        'time': 'Kemarin',
        'is_read': false,
      },
      {
        'id': 3,
        'type': 'approved',
        'title': 'Pinjaman Disetujui!',
        'message': 'Pengajuan Kasbon (Pay Later) sebesar Rp 500.000 telah disetujui oleh admin. Saldo sudah masuk ke dompet Anda.',
        'time': '12 Jun 2026',
        'is_read': true,
      },
      {
        'id': 4,
        'type': 'rejected',
        'title': 'Pinjaman Ditolak',
        'message': 'Mohon maaf, pengajuan Pinjaman Anda sebesar Rp 10.000.000 ditolak karena tidak memenuhi syarat limit kredit bulanan.',
        'time': '10 Jun 2026',
        'is_read': true,
      },
    ];

    return Scaffold(
      backgroundColor: surface,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        centerTitle: true,
        iconTheme: const IconThemeData(color: Color(0xFF0F172A)),
        title: Text(
          'Notifikasi',
          style: GoogleFonts.plusJakartaSans(
            color: const Color(0xFF0F172A),
            fontSize: 18,
            fontWeight: FontWeight.bold,
          ),
        ),
      ),
      body: ListView.separated(
        padding: const EdgeInsets.all(24),
        itemCount: dummyNotifications.length,
        separatorBuilder: (_, __) => const SizedBox(height: 16),
        itemBuilder: (context, index) {
          final notif = dummyNotifications[index];
          final String type = notif['type'];
          
          IconData icon;
          Color iconColor;
          Color bgColor;

          switch (type) {
            case 'overdue':
              icon = Icons.warning_rounded;
              iconColor = dangerRed;
              bgColor = const Color(0xFFFEF2F2); // red-50
              break;
            case 'upcoming':
              icon = Icons.calendar_today_rounded;
              iconColor = warningOrange;
              bgColor = const Color(0xFFFFFBEB); // amber-50
              break;
            case 'approved':
              icon = Icons.check_circle_rounded;
              iconColor = successGreen;
              bgColor = const Color(0xFFECFDF5); // emerald-50
              break;
            case 'rejected':
              icon = Icons.cancel_rounded;
              iconColor = dangerRed;
              bgColor = const Color(0xFFFEF2F2); // red-50
              break;
            default:
              icon = Icons.notifications_rounded;
              iconColor = primaryBlue;
              bgColor = const Color(0xFFEFF6FF); // blue-50
          }

          final bool isRead = notif['is_read'];

          return Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: isRead ? Colors.white : const Color(0xFFF8FAFC),
              borderRadius: BorderRadius.circular(20),
              border: Border.all(
                color: isRead ? const Color(0xFFF1F5F9) : primaryBlue.withValues(alpha: 0.2),
                width: isRead ? 1 : 1.5,
              ),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.02),
                  blurRadius: 10,
                  offset: const Offset(0, 4),
                ),
              ],
            ),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  width: 48,
                  height: 48,
                  decoration: BoxDecoration(
                    color: bgColor,
                    shape: BoxShape.circle,
                  ),
                  child: Icon(icon, color: iconColor, size: 24),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Expanded(
                            child: Text(
                              notif['title'],
                              style: GoogleFonts.plusJakartaSans(
                                color: const Color(0xFF0F172A),
                                fontSize: 14,
                                fontWeight: isRead ? FontWeight.w600 : FontWeight.bold,
                              ),
                            ),
                          ),
                          if (!isRead)
                            Container(
                              width: 8,
                              height: 8,
                              decoration: const BoxDecoration(
                                color: primaryBlue,
                                shape: BoxShape.circle,
                              ),
                            ),
                        ],
                      ),
                      const SizedBox(height: 6),
                      Text(
                        notif['message'],
                        style: GoogleFonts.inter(
                          color: const Color(0xFF475569),
                          fontSize: 13,
                          height: 1.4,
                        ),
                      ),
                      const SizedBox(height: 12),
                      Text(
                        notif['time'],
                        style: GoogleFonts.inter(
                          color: const Color(0xFF94A3B8),
                          fontSize: 11,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }
}
