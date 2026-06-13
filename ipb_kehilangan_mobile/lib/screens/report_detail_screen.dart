import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';

import '../services/api_service.dart';
import '../services/theme_service.dart';

class ReportDetailScreen extends StatefulWidget {
  const ReportDetailScreen({
    super.key,
    required this.reportId,
    this.initialReport,
    this.isOwnerView = false,
  });

  final String reportId;
  final Map<String, dynamic>? initialReport;
  final bool isOwnerView;

  @override
  State<ReportDetailScreen> createState() => _ReportDetailScreenState();
}

class _ReportDetailScreenState extends State<ReportDetailScreen> {
  static const Color primaryBlue = Color(0xFF31479B);

  Map<String, dynamic>? report;
  bool isLoading = true;
  bool hasChanged = false;

  @override
  void initState() {
    super.initState();
    report = widget.initialReport;
    loadReport();
  }

  Future<void> loadReport() async {
    try {
      final data = await ApiService.getReportDetail(widget.reportId);
      if (!mounted) return;

      setState(() {
        report = data;
        isLoading = false;
      });
    } catch (e) {
      if (!mounted) return;

      setState(() => isLoading = false);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(e.toString().replaceFirst('Exception: ', ''))),
      );
    }
  }

  Future<void> openWhatsApp() async {
    final phone = _phoneNumber(report?['user']);
    if (phone.isEmpty) {
      showMessage("Nomor WhatsApp poster tidak tersedia");
      return;
    }

    final cleanedPhone = _normalizePhone(phone);
    final title = (report?['title'] ?? 'barang').toString();
    final message = Uri.encodeComponent(
      "Halo, saya ingin bertanya tentang laporan $title di IPB Kehilangan.",
    );
    final url = Uri.parse("https://wa.me/$cleanedPhone?text=$message");

    if (!await launchUrl(url, mode: LaunchMode.externalApplication)) {
      showMessage("Tidak bisa membuka WhatsApp");
    }
  }

  Future<void> editReport() async {
    final data = report;
    if (data == null) return;

    final updated = await showModalBottomSheet<Map<String, dynamic>>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(22)),
      ),
      builder: (_) => _EditReportSheet(item: data),
    );

    if (updated == null) return;

    try {
      await ApiService.updateReport(widget.reportId, updated);
      hasChanged = true;
      await loadReport();
      if (!mounted) return;
      showMessage("Laporan berhasil diedit");
    } catch (e) {
      if (!mounted) return;
      showMessage(e.toString().replaceFirst('Exception: ', ''));
    }
  }

  Future<void> deleteReport() async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text("Hapus laporan?"),
        content: const Text("Laporan yang dihapus tidak bisa dikembalikan."),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text("Batal"),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text(
              "Hapus",
              style: TextStyle(color: Color(0xFFD24A4A)),
            ),
          ),
        ],
      ),
    );

    if (confirmed != true) return;

    try {
      await ApiService.deleteReport(widget.reportId);
      if (!mounted) return;
      Navigator.pop(context, true);
    } catch (e) {
      if (!mounted) return;
      showMessage(e.toString().replaceFirst('Exception: ', ''));
    }
  }

  void showMessage(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message)),
    );
  }

  @override
  Widget build(BuildContext context) {
    final data = report;

    return ValueListenableBuilder<bool>(
      valueListenable: ThemeService.isDarkMode,
      builder: (context, isDarkMode, _) {
        return Scaffold(
          backgroundColor: isDarkMode ? const Color(0xFF111827) : Colors.white,
          body: SafeArea(
            child: isLoading && data == null
                ? const Center(child: CircularProgressIndicator())
                : data == null
                    ? Center(
                        child: Text(
                          "Laporan tidak ditemukan",
                          style: TextStyle(
                            color: isDarkMode ? Colors.white : Colors.black,
                          ),
                        ),
                      )
                    : ListView(
                        padding: const EdgeInsets.fromLTRB(12, 16, 12, 32),
                        children: [
                          _BackButton(
                            onBack: () => Navigator.pop(context, hasChanged),
                            isDarkMode: isDarkMode,
                          ),
                          const SizedBox(height: 18),
                          _ReportCard(item: data, isDarkMode: isDarkMode),
                          const SizedBox(height: 28),
                          if (widget.isOwnerView)
                            _OwnerActions(
                              onEdit: editReport,
                              onDelete: deleteReport,
                            )
                          else
                            Padding(
                              padding: const EdgeInsets.symmetric(horizontal: 24),
                              child: SizedBox(
                                height: 66,
                                child: ElevatedButton.icon(
                                  onPressed: openWhatsApp,
                                  style: ElevatedButton.styleFrom(
                                    elevation: 0,
                                    backgroundColor: const Color(0xFF46D128),
                                    foregroundColor: Colors.white,
                                    shape: RoundedRectangleBorder(
                                      borderRadius: BorderRadius.circular(34),
                                    ),
                                  ),
                                  icon: Container(
                                    width: 36,
                                    height: 36,
                                    decoration: const BoxDecoration(
                                      color: Colors.white,
                                      shape: BoxShape.circle,
                                    ),
                                    child: const Icon(
                                      Icons.call,
                                      color: Color(0xFF46D128),
                                      size: 23,
                                    ),
                                  ),
                                  label: const Text(
                                    "Hubungi Original Poster VIA Wa",
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                    textAlign: TextAlign.center,
                                    style: TextStyle(
                                      fontSize: 15,
                                      fontWeight: FontWeight.w800,
                                    ),
                                  ),
                                ),
                              ),
                            ),
                        ],
                      ),
          ),
        );
      },
    );
  }

  String _phoneNumber(dynamic user) {
    if (user is Map && user['phone'] != null) {
      return user['phone'].toString();
    }

    return '';
  }

  String _normalizePhone(String value) {
    var phone = value.replaceAll(RegExp(r'[^0-9+]'), '');

    if (phone.startsWith('+')) {
      phone = phone.substring(1);
    }

    if (phone.startsWith('0')) {
      phone = '62${phone.substring(1)}';
    }

    return phone;
  }
}

class _OwnerActions extends StatelessWidget {
  const _OwnerActions({
    required this.onEdit,
    required this.onDelete,
  });

  final VoidCallback onEdit;
  final VoidCallback onDelete;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 24),
      child: Row(
        children: [
          Expanded(
            child: SizedBox(
              height: 50,
              child: ElevatedButton.icon(
                onPressed: onEdit,
                style: ElevatedButton.styleFrom(
                  elevation: 0,
                  backgroundColor: _ReportDetailScreenState.primaryBlue,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(26),
                  ),
                ),
                icon: const Icon(Icons.edit, size: 20),
                label: const Text(
                  "Edit",
                  style: TextStyle(fontSize: 15, fontWeight: FontWeight.w800),
                ),
              ),
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: SizedBox(
              height: 50,
              child: ElevatedButton.icon(
                onPressed: onDelete,
                style: ElevatedButton.styleFrom(
                  elevation: 0,
                  backgroundColor: const Color(0xFFD24A4A),
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(26),
                  ),
                ),
                icon: const Icon(Icons.delete, size: 20),
                label: const Text(
                  "Hapus",
                  style: TextStyle(fontSize: 15, fontWeight: FontWeight.w800),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _EditReportSheet extends StatefulWidget {
  const _EditReportSheet({required this.item});

  final Map<String, dynamic> item;

  @override
  State<_EditReportSheet> createState() => _EditReportSheetState();
}

class _EditReportSheetState extends State<_EditReportSheet> {
  final formKey = GlobalKey<FormState>();
  late final TextEditingController titleController;
  late final TextEditingController locationController;
  late final TextEditingController descriptionController;
  late String selectedType;

  @override
  void initState() {
    super.initState();
    titleController = TextEditingController(
      text: (widget.item['title'] ?? '').toString(),
    );
    locationController = TextEditingController(
      text: (widget.item['location'] ?? '').toString(),
    );
    descriptionController = TextEditingController(
      text: (widget.item['description'] ?? '').toString(),
    );
    final type = (widget.item['type'] ?? 'Dicari').toString();
    selectedType = type == 'Ditemukan' ? 'Ditemukan' : 'Dicari';
  }

  @override
  void dispose() {
    titleController.dispose();
    locationController.dispose();
    descriptionController.dispose();
    super.dispose();
  }

  void submit() {
    if (!formKey.currentState!.validate()) return;

    Navigator.pop(context, {
      "title": titleController.text.trim(),
      "location": locationController.text.trim(),
      "description": descriptionController.text.trim(),
      "type": selectedType,
    });
  }

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.only(
        left: 18,
        right: 18,
        top: 18,
        bottom: MediaQuery.of(context).viewInsets.bottom + 18,
      ),
      child: Form(
        key: formKey,
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Text(
              "Edit Laporan",
              style: TextStyle(
                color: _ReportDetailScreenState.primaryBlue,
                fontSize: 20,
                fontWeight: FontWeight.w900,
              ),
            ),
            const SizedBox(height: 18),
            _EditTextField(
              controller: titleController,
              hintText: "Nama Barang",
            ),
            const SizedBox(height: 10),
            _EditTextField(
              controller: locationController,
              hintText: "Lokasi",
            ),
            const SizedBox(height: 10),
            _EditTextField(
              controller: descriptionController,
              hintText: "Deskripsi",
              minLines: 3,
              maxLines: 5,
            ),
            const SizedBox(height: 10),
            DropdownButtonFormField<String>(
              value: selectedType,
              items: const [
                DropdownMenuItem(value: "Dicari", child: Text("Dicari")),
                DropdownMenuItem(value: "Ditemukan", child: Text("Ditemukan")),
              ],
              onChanged: (value) {
                if (value == null) return;
                setState(() => selectedType = value);
              },
              decoration: const InputDecoration(
                border: OutlineInputBorder(),
                contentPadding: EdgeInsets.symmetric(
                  horizontal: 14,
                  vertical: 12,
                ),
              ),
            ),
            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              height: 46,
              child: ElevatedButton(
                onPressed: submit,
                style: ElevatedButton.styleFrom(
                  elevation: 0,
                  backgroundColor: _ReportDetailScreenState.primaryBlue,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(24),
                  ),
                ),
                child: const Text(
                  "Simpan",
                  style: TextStyle(fontWeight: FontWeight.w800),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _EditTextField extends StatelessWidget {
  const _EditTextField({
    required this.controller,
    required this.hintText,
    this.minLines = 1,
    this.maxLines = 1,
  });

  final TextEditingController controller;
  final String hintText;
  final int minLines;
  final int maxLines;

  @override
  Widget build(BuildContext context) {
    return TextFormField(
      controller: controller,
      minLines: minLines,
      maxLines: maxLines,
      validator: (value) {
        if (value == null || value.trim().isEmpty) {
          return "$hintText wajib diisi";
        }

        return null;
      },
      decoration: InputDecoration(
        hintText: hintText,
        border: const OutlineInputBorder(),
        contentPadding: const EdgeInsets.symmetric(
          horizontal: 14,
          vertical: 12,
        ),
      ),
    );
  }
}

class _BackButton extends StatelessWidget {
  const _BackButton({
    required this.onBack,
    required this.isDarkMode,
  });

  final VoidCallback onBack;
  final bool isDarkMode;

  @override
  Widget build(BuildContext context) {
    return Align(
      alignment: Alignment.centerLeft,
      child: TextButton.icon(
        onPressed: onBack,
        icon: Icon(
          Icons.chevron_left,
          color: isDarkMode ? Colors.white : Colors.black,
          size: 30,
        ),
        label: const Text(
          "Back",
          style: TextStyle(
            color: _ReportDetailScreenState.primaryBlue,
            fontSize: 16,
            fontWeight: FontWeight.w800,
          ),
        ),
        style: TextButton.styleFrom(
          padding: EdgeInsets.zero,
          minimumSize: const Size(84, 38),
          alignment: Alignment.centerLeft,
        ),
      ),
    );
  }
}

class _ReportCard extends StatelessWidget {
  const _ReportCard({
    required this.item,
    required this.isDarkMode,
  });

  final Map<String, dynamic> item;
  final bool isDarkMode;

  @override
  Widget build(BuildContext context) {
    final title = (item['title'] ?? '-').toString();
    final location = (item['location'] ?? '-').toString();
    final description = (item['description'] ?? '-').toString();
    final type = (item['type'] ?? 'Dicari').toString();
    final user = item['user'];
    final name = _userValue(user, 'name', fallback: 'User');
    final email = _userValue(user, 'email');
    final phone = _userValue(user, 'phone');
    final createdAt = _ReportTime.from(item['created_at']);
    final imageUrl = _firstImageUrl(item['images']);
    final isFound = type.toLowerCase().contains('ditemukan');

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.only(left: 14, bottom: 2),
            child: Text(
              createdAt.relativeLabel,
              style: const TextStyle(color: Color(0xFF9B9B9B), fontSize: 13),
            ),
          ),
          Container(
            padding: const EdgeInsets.fromLTRB(14, 12, 14, 12),
            decoration: BoxDecoration(
              color: isDarkMode ? const Color(0xFF1F2937) : Colors.white,
              borderRadius: BorderRadius.circular(10),
              border: Border.all(
                color: isDarkMode
                    ? const Color(0xFF374151)
                    : const Color(0xFFD8D8D8),
                width: 3,
              ),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            name,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: TextStyle(
                              color: isDarkMode ? Colors.white : Colors.black,
                              fontSize: 15,
                              fontWeight: FontWeight.w900,
                            ),
                          ),
                          if (email.isNotEmpty)
                            Text(
                              '@${email.split('@').first}',
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: TextStyle(
                                color: isDarkMode ? Colors.white : Colors.black,
                                fontSize: 13,
                                height: 1.1,
                              ),
                            ),
                          if (phone.isNotEmpty)
                            Text(
                              phone,
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: TextStyle(
                                color: isDarkMode ? Colors.white : Colors.black,
                                fontSize: 13,
                                height: 1.1,
                              ),
                            ),
                        ],
                      ),
                    ),
                    Icon(
                      Icons.more_horiz,
                      color: isDarkMode ? Colors.white : Colors.black,
                      size: 28,
                    ),
                  ],
                ),
                const SizedBox(height: 14),
                ClipRRect(
                  borderRadius: BorderRadius.circular(10),
                  child: SizedBox(
                    height: 198,
                    width: double.infinity,
                    child: imageUrl.isEmpty
                        ? const _ImageFallback()
                        : Image.network(
                            imageUrl,
                            fit: BoxFit.cover,
                            errorBuilder: (_, __, ___) =>
                                const _ImageFallback(),
                          ),
                  ),
                ),
                const SizedBox(height: 14),
                Text(
                  title,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    color: isDarkMode ? Colors.white : Colors.black,
                    fontSize: 16,
                    height: 1,
                    fontWeight: FontWeight.w900,
                  ),
                ),
                const SizedBox(height: 3),
                Row(
                  children: [
                    Icon(
                      Icons.location_on,
                      size: 16,
                      color: isDarkMode ? Colors.white : Colors.black,
                    ),
                    Expanded(
                      child: Text(
                        location,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: TextStyle(
                          color: isDarkMode ? Colors.white : Colors.black,
                          fontSize: 13,
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                Text(
                  description,
                  style: TextStyle(
                    color: isDarkMode ? Colors.white : Colors.black,
                    fontSize: 13,
                    height: 1.25,
                  ),
                ),
                const SizedBox(height: 12),
                Align(
                  alignment: Alignment.centerRight,
                  child: Container(
                    width: 66,
                    height: 28,
                    alignment: Alignment.center,
                    decoration: BoxDecoration(
                      color: isFound
                          ? const Color(0xFF20C73B)
                          : const Color(0xFFD24A4A),
                      borderRadius: BorderRadius.circular(16),
                    ),
                    child: Text(
                      isFound ? "Ditemukan" : "Dicari",
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 13,
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  String _userValue(dynamic user, String key, {String fallback = ''}) {
    if (user is Map && user[key] != null) {
      final value = user[key].toString().trim();
      if (value.isNotEmpty) {
        return value;
      }
    }

    return fallback;
  }

  String _firstImageUrl(dynamic images) {
    if (images is List && images.isNotEmpty) {
      return images.first?.toString() ?? '';
    }

    return '';
  }
}

class _ReportTime {
  const _ReportTime(this.dateTime);

  final DateTime? dateTime;

  factory _ReportTime.from(dynamic value) {
    if (value == null) {
      return const _ReportTime(null);
    }

    if (value is Map && value[r'$date'] != null) {
      return _ReportTime.from(value[r'$date']);
    }

    final parsed = DateTime.tryParse(value.toString());
    return _ReportTime(parsed?.toLocal());
  }

  String get relativeLabel {
    if (dateTime == null) {
      return '-';
    }

    final difference = DateTime.now().difference(dateTime!);

    if (difference.inMinutes < 1) {
      return 'baru saja';
    }

    if (difference.inMinutes < 60) {
      return '${difference.inMinutes} menit yang lalu';
    }

    if (difference.inHours < 24) {
      return '${difference.inHours} jam yang lalu';
    }

    if (difference.inDays < 7) {
      return '${difference.inDays} hari yang lalu';
    }

    return '${dateTime!.day}/${dateTime!.month}/${dateTime!.year}';
  }
}

class _ImageFallback extends StatelessWidget {
  const _ImageFallback();

  @override
  Widget build(BuildContext context) {
    return Container(
      color: const Color(0xFFF2F2F2),
      child: const Center(
        child: Icon(Icons.image_outlined, color: Color(0xFFBDBDBD), size: 54),
      ),
    );
  }
}
