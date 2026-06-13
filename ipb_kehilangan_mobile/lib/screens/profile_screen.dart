import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../services/api_service.dart';
import '../services/theme_service.dart';
import 'edit_profile_screen.dart';
import 'login_screen.dart';
import 'report_detail_screen.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  static const Color primaryBlue = Color(0xFF31479B);

  Map<String, dynamic>? user;
  List reports = [];
  bool isLoading = true;
  final imagePicker = ImagePicker();

  @override
  void initState() {
    super.initState();
    loadData();
  }

  Future<void> loadData() async {
    setState(() => isLoading = true);

    try {
      final results = await Future.wait([
        ApiService.getCurrentUser(),
        ApiService.getMyReports(search: "", filter: "Lihat Semua"),
      ]);

      setState(() {
        user = results[0] as Map<String, dynamic>?;
        reports = results[1] as List;
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

  Future<void> toggleReportStatus(Map<String, dynamic> item) async {
    final id = (item['id'] ?? item['_id'] ?? '').toString();
    if (id.isEmpty) return;

    final type = (item['type'] ?? 'Dicari').toString();
    final currentStatus = (item['status'] ?? '').toString();
    final nextStatus = currentStatus == "selesai" ? type.toLowerCase() : "selesai";

    try {
      await ApiService.updateReportStatus(id, nextStatus);
      await loadData();
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(e.toString().replaceFirst('Exception: ', ''))),
      );
    }
  }

  Future<void> openReportDetail(Map<String, dynamic> item) async {
    final id = (item['id'] ?? item['_id'] ?? '').toString();
    if (id.isEmpty) return;

    final shouldRefresh = await Navigator.push<bool>(
      context,
      MaterialPageRoute(
        builder: (_) => ReportDetailScreen(
          reportId: id,
          initialReport: item,
          isOwnerView: true,
        ),
      ),
    );

    if (shouldRefresh == true && mounted) {
      loadData();
    }
  }

  Future<void> openEditProfile() async {
    final currentUser = user;
    if (currentUser == null) return;

    final updatedUser = await Navigator.push<Map<String, dynamic>>(
      context,
      MaterialPageRoute(
        builder: (_) => EditProfileScreen(user: currentUser),
      ),
    );

    if (updatedUser != null && mounted) {
      setState(() => user = updatedUser);
      loadData();
    }
  }

  Future<void> updateProfilePhoto() async {
    final currentUser = user;
    if (currentUser == null) return;

    final source = await showModalBottomSheet<ImageSource>(
      context: context,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (_) => SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            ListTile(
              leading: const Icon(Icons.photo_camera_outlined),
              title: const Text("Kamera"),
              onTap: () => Navigator.pop(context, ImageSource.camera),
            ),
            ListTile(
              leading: const Icon(Icons.photo_library_outlined),
              title: const Text("Galeri"),
              onTap: () => Navigator.pop(context, ImageSource.gallery),
            ),
          ],
        ),
      ),
    );

    if (source == null) return;

    final photo = await imagePicker.pickImage(source: source, imageQuality: 85);
    if (photo == null) return;

    try {
      final updatedUser = await ApiService.updateProfile(
        data: {
          "name": (currentUser['name'] ?? '').toString(),
          "phone": (currentUser['phone'] ?? '').toString(),
          "gender": (currentUser['gender'] ?? 'Laki-Laki').toString(),
          "nim": (currentUser['nim'] ?? '').toString(),
        },
        profilePhoto: photo,
      );

      if (!mounted) return;

      setState(() => user = updatedUser);
    } catch (e) {
      if (!mounted) return;

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(e.toString().replaceFirst('Exception: ', ''))),
      );
    }
  }

  Future<void> logout() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('token');

    if (!mounted) return;

    Navigator.pushAndRemoveUntil(
      context,
      MaterialPageRoute(builder: (_) => const LoginScreen()),
      (_) => false,
    );
  }

  @override
  Widget build(BuildContext context) {
    final name = (user?['name'] ?? 'User').toString();
    final profilePhoto = (user?['profile_photo'] ?? '').toString();

    return ValueListenableBuilder<bool>(
      valueListenable: ThemeService.isDarkMode,
      builder: (context, isDarkMode, _) {
        return Scaffold(
          backgroundColor: isDarkMode ? const Color(0xFF111827) : Colors.white,
          body: SafeArea(
            child: isLoading
                ? const Center(child: CircularProgressIndicator())
                : SingleChildScrollView(
                    child: Column(
                      children: [
                        _ProfileHeader(
                          name: name,
                          profilePhotoUrl: profilePhoto.isEmpty ? null : profilePhoto,
                          isDarkMode: isDarkMode,
                          onDarkModeChanged: (value) {
                            ThemeService.setDarkMode(value);
                          },
                          onLogout: logout,
                          onEditProfile: openEditProfile,
                          onAddPhoto: updateProfilePhoto,
                        ),
                        
                        // Garis pemisah atau spasi
                        Padding(
                          padding: const EdgeInsets.symmetric(vertical: 8),
                          child: Container(
                            height: 1,
                            color: isDarkMode ? Colors.grey[800] : Colors.grey[300],
                            width: double.infinity,
                          ),
                        ),

                        // Postingan (Laporan)
                        if (reports.isEmpty)
                          Padding(
                            padding: const EdgeInsets.all(32.0),
                            child: Text(
                              "Belum ada laporan",
                              style: TextStyle(
                                color: isDarkMode ? const Color(0xFFB8B8B8) : const Color(0xFF777777),
                              ),
                            ),
                          )
                        else
                          GridView.builder(
                            shrinkWrap: true,
                            physics: const NeverScrollableScrollPhysics(),
                            padding: const EdgeInsets.fromLTRB(16, 12, 16, 120),
                            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                              crossAxisCount: 2,
                              childAspectRatio: 0.65,
                              crossAxisSpacing: 16,
                              mainAxisSpacing: 16,
                            ),
                            itemCount: reports.length,
                            itemBuilder: (context, index) {
                              final item = reports[index];
                              return _MyReportCard(
                                item: item is Map ? Map<String, dynamic>.from(item) : const <String, dynamic>{},
                                onStatusPressed: toggleReportStatus,
                                onTap: openReportDetail,
                                isDarkMode: isDarkMode,
                              );
                            },
                          ),
                      ],
                    ),
                  ),
          ),
        );
      },
    );
  }
}

class _ProfileHeader extends StatelessWidget {
  const _ProfileHeader({
    required this.name,
    required this.profilePhotoUrl,
    required this.isDarkMode,
    required this.onDarkModeChanged,
    required this.onLogout,
    required this.onEditProfile,
    required this.onAddPhoto,
  });

  final String name;
  final String? profilePhotoUrl;
  final bool isDarkMode;
  final ValueChanged<bool> onDarkModeChanged;
  final VoidCallback onLogout;
  final VoidCallback onEditProfile;
  final VoidCallback onAddPhoto;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.fromLTRB(12, 16, 12, 26),
      decoration: BoxDecoration(
        color: isDarkMode ? const Color(0xFF1F2937) : Colors.white,
      ),
      child: Column(
        children: [
          const SizedBox(height: 16),
          Text(
            "Laporan Saya",
            style: TextStyle(
              color: isDarkMode ? Colors.white : Colors.black,
              fontSize: 25,
              fontWeight: FontWeight.w800,
            ),
          ),
          const SizedBox(height: 16),
          _LargeProfileAvatar(
            imageUrl: profilePhotoUrl,
            onAddPhoto: onAddPhoto,
          ),
          const SizedBox(height: 10),
          Text(
            name,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: TextStyle(
              color: isDarkMode ? Colors.white : Colors.black,
              fontSize: 26,
              fontWeight: FontWeight.w500,
            ),
          ),
          const SizedBox(height: 10),
          TextButton(
            onPressed: onEditProfile,
            child: const Text(
              "Edit Profil",
              style: TextStyle(color: Color(0xFF9B9B9B), fontSize: 16),
            ),
          ),
          const SizedBox(height: 8),
          _ProfileActions(
            isDarkMode: isDarkMode,
            onDarkModeChanged: onDarkModeChanged,
            onLogout: onLogout,
          ),
        ],
      ),
    );
  }
}

class _ProfileActions extends StatelessWidget {
  const _ProfileActions({
    required this.isDarkMode,
    required this.onDarkModeChanged,
    required this.onLogout,
  });

  final bool isDarkMode;
  final ValueChanged<bool> onDarkModeChanged;
  final VoidCallback onLogout;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 8),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Container(
            height: 50,
            width: 218,
            padding: const EdgeInsets.only(left: 14, right: 6),
            decoration: BoxDecoration(
              color:
                  isDarkMode ? const Color(0xFF111827) : const Color(0xFFE9E9E9),
              borderRadius: BorderRadius.circular(25),
            ),
            child: Row(
              children: [
                Icon(
                  isDarkMode ? Icons.dark_mode : Icons.light_mode,
                  color: _ProfileScreenState.primaryBlue,
                  size: 23,
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    "Dark Mode",
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: TextStyle(
                      color: isDarkMode ? Colors.white : Colors.black,
                      fontSize: 13,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                ),
                Transform.scale(
                  scale: 0.78,
                  child: Switch(
                    value: isDarkMode,
                    activeColor: _ProfileScreenState.primaryBlue,
                    onChanged: onDarkModeChanged,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(width: 12),
          SizedBox(
            height: 50,
            width: 118,
            child: ElevatedButton.icon(
              onPressed: onLogout,
              style: ElevatedButton.styleFrom(
                elevation: 0,
                backgroundColor: const Color(0xFFD24A4A),
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 14),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(24),
                ),
              ),
              icon: const Icon(Icons.logout, size: 18),
              label: const Text(
                "Logout",
                style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _LargeProfileAvatar extends StatelessWidget {
  const _LargeProfileAvatar({
    required this.imageUrl,
    required this.onAddPhoto,
  });

  final String? imageUrl;
  final VoidCallback onAddPhoto;

  @override
  Widget build(BuildContext context) {
    Widget avatar = const CircleAvatar(
      radius: 50,
      backgroundColor: _ProfileScreenState.primaryBlue,
      child: Icon(Icons.person, color: Colors.white, size: 70),
    );

    if (imageUrl != null && imageUrl!.isNotEmpty) {
      avatar = ClipOval(
        child: Image.network(
          imageUrl!,
          width: 100,
          height: 100,
          fit: BoxFit.cover,
          errorBuilder: (_, __, ___) => const CircleAvatar(
            radius: 50,
            backgroundColor: _ProfileScreenState.primaryBlue,
            child: Icon(Icons.person, color: Colors.white, size: 70),
          ),
        ),
      );
    }

    return Stack(
      clipBehavior: Clip.none,
      children: [
        avatar,
        Positioned(
          right: 4,
          bottom: 8,
          child: GestureDetector(
            onTap: onAddPhoto,
            child: Container(
              width: 22,
              height: 22,
              decoration: const BoxDecoration(
                color: Colors.black,
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.add, color: Colors.white, size: 18),
            ),
          ),
        ),
      ],
    );
  }
}


class _MyReportCard extends StatelessWidget {
  const _MyReportCard({
    required this.item,
    required this.onStatusPressed,
    required this.onTap,
    required this.isDarkMode,
  });

  final Map<String, dynamic> item;
  final ValueChanged<Map<String, dynamic>> onStatusPressed;
  final ValueChanged<Map<String, dynamic>> onTap;
  final bool isDarkMode;

  @override
  Widget build(BuildContext context) {
    final title = (item['title'] ?? '-').toString();
    final location = (item['location'] ?? '-').toString();
    final description = (item['description'] ?? '-').toString();
    final type = (item['type'] ?? 'Dicari').toString();
    final images = item['images'];
    final imageUrl =
        images is List && images.isNotEmpty ? images.first?.toString() : null;
    final status = (item['status'] ?? '').toString();
    final isDone = status == "selesai";

    return Material(
      color: isDarkMode ? const Color(0xFF1F2937) : Colors.white,
      borderRadius: BorderRadius.circular(18),
      child: InkWell(
        onTap: () => onTap(item),
        borderRadius: BorderRadius.circular(18),
        child: Container(
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            border: Border.all(
              color: isDarkMode
                  ? const Color(0xFF374151)
                  : const Color(0xFFD8D8D8),
              width: 3,
            ),
            borderRadius: BorderRadius.circular(18),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  _StatusPill(label: isDone ? "Selesai" : type),
                  const Spacer(),
                  Icon(
                    Icons.more_horiz,
                    size: 22,
                    color: isDarkMode ? Colors.white : Colors.black,
                  ),
                ],
              ),
              const SizedBox(height: 10),
              ClipRRect(
                borderRadius: BorderRadius.circular(8),
                child: SizedBox(
                  height: 118,
                  width: double.infinity,
                  child: imageUrl != null && imageUrl.isNotEmpty
                      ? Image.network(
                          imageUrl,
                          fit: BoxFit.cover,
                          errorBuilder: (_, __, ___) =>
                              const _CardImageFallback(),
                        )
                      : const _CardImageFallback(),
                ),
              ),
              const SizedBox(height: 8),
              Text(
                title,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: TextStyle(
                  color: isDarkMode ? Colors.white : Colors.black,
                  fontSize: 14,
                  fontWeight: FontWeight.w800,
                ),
              ),
              const SizedBox(height: 2),
              Row(
                children: [
                  Icon(
                    Icons.location_pin,
                    color: isDarkMode ? Colors.white : Colors.black,
                    size: 14,
                  ),
                  Expanded(
                    child: Text(
                      location,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: TextStyle(
                        color: isDarkMode ? Colors.white : Colors.black,
                        fontSize: 12,
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              Expanded(
                child: Text(
                  description,
                  maxLines: 4,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    color: isDarkMode ? Colors.white : Colors.black,
                    fontSize: 12,
                    height: 1.25,
                  ),
                ),
              ),
              SizedBox(
                width: double.infinity,
                height: 28,
                child: ElevatedButton(
                  onPressed: () => onStatusPressed(item),
                  style: ElevatedButton.styleFrom(
                    elevation: 0,
                    backgroundColor: const Color(0xFFD24A4A),
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(20),
                    ),
                  ),
                  child: Text(
                    isDone ? "Belum Selesai" : "Tandai Selesai",
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _StatusPill extends StatelessWidget {
  const _StatusPill({required this.label});

  final String label;

  @override
  Widget build(BuildContext context) {
    final normalized = label.toLowerCase();
    final color = normalized == "selesai"
        ? const Color(0xFF31CF2D)
        : normalized == "ditemukan"
            ? const Color(0xFFE6B31B)
            : const Color(0xFFD24A4A);

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
      decoration: BoxDecoration(
        color: color,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Text(
        label,
        style: const TextStyle(
          color: Colors.white,
          fontSize: 10,
          fontWeight: FontWeight.w800,
        ),
      ),
    );
  }
}

class _CardImageFallback extends StatelessWidget {
  const _CardImageFallback();

  @override
  Widget build(BuildContext context) {
    return Container(
      color: const Color(0xFFF2F2F2),
      child: const Center(
        child: Icon(Icons.image_outlined, color: Color(0xFFBDBDBD), size: 44),
      ),
    );
  }
}
